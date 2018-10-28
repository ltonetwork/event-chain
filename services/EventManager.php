<?php

use Jasny\ValidationResult;
use Jasny\DB\Entity\Identifiable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use LTO\Account;

/**
 * Service to handle new events.
 */
class EventManager
{
    /**
     * @var string
     */
    protected $chain;
    
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;
    
    /**
     * @var ResourceStorage
     */
    protected $resourceStorage;
    
    /**
     * @var DispatcherManager
     */
    protected $dispatcher;
    
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var Account
     */
    protected $node;
    
    /**
     * @var AnchorClient
     */
    protected $anchor;

    
    /**
     * Class constructor
     * 
     * @param ResourceFactory   $resourceFactory
     * @param ResourceStorage   $resourceStorage
     * @param DispatcherManager $dispatcher
     * @param EventFactory      $eventFactory
     * @param AnchorClient            $anchor
     */
    public function __construct(
        ResourceFactory $resourceFactory,
        ResourceStorage $resourceStorage,
        DispatcherManager $dispatcher,
        EventFactory $eventFactory,
        AnchorClient $anchor
    )
    {
        $this->resourceFactory = $resourceFactory;
        $this->resourceStorage = $resourceStorage;
        $this->dispatcher = $dispatcher;
        $this->eventFactory = $eventFactory;
        $this->node = $eventFactory->getNodeAccount();
        $this->anchor = $anchor;
    }

    /**
     * Create a manager for specified chain.
     *
     * @param EventChain $chain
     * @return static
     * @throws UnexpectedValueException for a partial chain
     */
    public function with(EventChain $chain): self
    {
        if (isset($this->chain)) {
            throw new BadMethodCallException("Chain already set");
        }

        if ($chain->isPartial()) {
            throw new UnexpectedValueException("Event chain doesn't contain the genesis event");
        }

        $clone = clone $this;
        $clone->chain = $chain;

        return $clone;
    }


    /**
     * Assert that a chain has been set
     *
     * @return void
     * @throw BadMethodCallException if no chain is set.
     */
    protected function assertChain(): void
    {
        if (!isset($this->chain)) {
            throw new BadMethodCallException("Chain not set; use the `withChain()` method.");
        }
    }


    /**
     * Add new events
     * 
     * @param EventChain $newEvents
     * @return ValidationResult
     */
    public function add(EventChain $newEvents)
    {
        $this->assertChain();

        if ($this->chain->id !== $newEvents->id) {
            throw new UnexpectedValueException("Can't add events of a different chain");
        }
        
        $validation = $newEvents->validate();
        
        if (!$validation->isSuccess()) {
            return $validation;
        }
        
        $previous = $newEvents->getFirstEvent()->previous;
        $oldNodes = $this->chain->getNodes();
        
        try {
            $following = $this->chain->getEventsAfter($previous);
        } catch (OutOfBoundsException $e) {
            return ValidationResult::error("events don't fit on chain, '%s' not found", $previous);
        }
        
        $next = reset($following);
        
        foreach ($newEvents->events as $event) {
            if ($next === false) {
                $first = $first ?? $event->previous;
                $handled = $this->handleNewEvent($event);
                $validation->add($handled, "event '$event->hash': ");
            } elseif ($event->hash !== $next->hash) {
                $validation->addError("fork detected; conflict on '%s' and '%s'", $event->hash, $next->hash);
            }
            
            $next = next($following);
            
            if ($validation->failed()) {
                $this->handleFailedEvent($event, $validation);
                break;
            }
            
            $this->chain->save();
        }
        
        if (isset($first)) {
            $this->dispatch($first, $oldNodes);
        }
        
        if ($validation->succeeded() && $this->chain->isEventSignedByAccount($this->chain->getLastEvent(), $this->node)) {
            $this->resourceStorage->done($this->chain);
        }
        
        return $validation;
    }
    
    
    /**
     * Add an event to the event chain.
     * 
     * @param Event $event
     * @return ValidationResult
     */
    public function handleNewEvent(Event $event)
    {
        $this->assertChain();

        $validation = $this->validateNewEvent($event);

        if ($validation->failed()) {
            return $validation;
        }

        $resource = $this->resourceFactory->extractFrom($event);
        $auth = $this->applyPrivilegeToResource($resource, $event);

        $validation->add($auth);
        $validation->add($resource->validate());

        if ($validation->failed()) {
            return $validation;
        }

        $validation = $this->storeResource($resource);
        if ($validation->failed()) {
            return $validation;
        }

        if ($this->chain->isEventSignedByAccount($event, $this->node)) {
            $this->anchor->hash($event->hash);
        }

        $this->chain->events->add($event);

        return ValidationResult::success();
    }
    
    /**
     * Add an error event to the event chain.
     * 
     * @param Event             $event
     * @param ValidationResult  $validation  The validation that failed
     */
    public function handleFailedEvent(Event $event, ValidationResult $validation)
    {
        $this->assertChain();

        $after = $this->chain->getEventsAfter($event->previous);
        $errorEvent = $this->eventFactory->createErrorEvent($validation->getErrors(), $after);
        $this->chain->events->add($errorEvent);
    }
    
    
    /**
     * Validate an event before adding it to the chain
     *
     * @param Event $event
     * @return ValidationResult
     */
    protected function validateNewEvent(Event $event)
    {
        $validation = $event->validate();

        if ($event->previous !== $this->chain->getLatestHash()) {
            $validation->addError("event '%s' doesn't fit on chain", $event->hash);
        }

        return $validation;
    }

    /**
     * Store a new event and add it to the chain
     *
     * @param Resource $resource
     * @return ValidationResult
     */
    protected function storeResource(Resource $resource)
    {
        try {
            $this->resourceStorage->store($resource);
        } catch (RequestException $e) {
            $id = 'resource' . ($resource instanceof Identifiable ? ' ' . $resource->getId() : '');
            $reason = $e instanceof ClientException ? $e->getMessage() : 'Server error';

            trigger_error($e->getMessage(), E_USER_WARNING);

            return ValidationResult::error("Failed to store %s: %s", $id, $reason);
        }

        $this->chain->registerResource($resource);

        return ValidationResult::success();
    }


    /**
     * Apply privilege to a resource.
     * Returns false if identity has no privileges to resource.
     * 
     * @param Resource $resource
     * @param Event    $event
     * @return boolean
     */
    public function applyPrivilegeToResource(Resource $resource, Event $event)
    {
        $this->assertChain();

        if ($this->chain->isEmpty()) {
            return $resource instanceof Identity ?
                ValidationResult::success() :
                ValidationResult::error("initial resource must be an identity");
        }
        
        $identities = $this->chain->identities->filterOnSignkey($event->signkey);
        $privileges = $identities->getPrivileges($resource);

        if (empty($privileges)) {
            return ValidationResult::error("no privileges for event");
        }

        $resource->applyPrivilege($this->consolidatedPrivilege($resource, $privileges));
        $resource->setIdentity($identities[0]);
        
        return ValidationResult::success();
    }
    
    /**
     * Create a consolidated privilege from an array of privileges
     * 
     * @param Resource    $resource
     * @param Privilege[] $privileges
     * @return Privilege
     */
    protected function consolidatedPrivilege(Resource $resource, array $privileges)
    {
        return Privilege::create($resource)->consolidate($privileges);
    }
    
    /**
     * Send a partial or full chain to the event dispatcher service
     * 
     * @param string   $first     The hash of the event from which the partial chain should be created
     * @param string[] $oldNodes  The old nodes of the chain before it was updated
     */
    protected function dispatch($first, $oldNodes = [])
    {
        $systemNodes = $this->chain->getNodesForSystem($this->node->getPublicSignKey());
        $otherNodes = array_unique(array_values(array_diff($oldNodes, $systemNodes)));

        // send partial chain to old nodes
        $partial = $this->chain->getPartialAfter($first);
        if (!empty($partial) && !empty($otherNodes)) {
            $this->dispatcher->dispatch($partial, $otherNodes);
        }

        // send full node to new nodes
        $newNodes = array_unique(array_values(array_diff($this->chain->getNodes(), $oldNodes, $systemNodes)));
        if (!empty($newNodes)) {
            $this->dispatcher->dispatch($this->chain, $newNodes);
        }
    }
}
