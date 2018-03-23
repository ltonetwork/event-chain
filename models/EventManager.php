<?php

use Jasny\ValidationResult;

/**
 * Handle new events
 */
class EventManager
{
    /**
     * @var string
     */
    protected $chain;
    
    /**
     * @var ResourceManager
     */
    protected $resourceManager;
    
    
    /**
     * Class constructor
     * 
     * @param EventChain $chain
     */
    public function __construct(EventChain $chain, ResourceManager $resourceManager)
    {
        if ($chain->isPartial()) {
            throw new UnexpectedValueException("Event chain doesn't contain the genesis event");
        }
        
        $this->chain = $chain;
        $this->resourceManager = $resourceManager;
    }
    
    /**
     * Add new events
     * 
     * @param EventChain $newEvents
     * @return ValidationResult
     */
    public function add(EventChain $newEvents)
    {
        if ($this->chain->id !== $newEvents->id) {
            throw new UnexpectedValueException("Can't add events of a different chain");
        }
        
        $validation = $newEvents->validate();
        
        if (!$validation->isSuccess()) {
            return $validation;
        }
        
        $previous = $newEvents->getFirstEvent()->previous;
        
        try {
            $following = $this->chain->getEventsAfter($previous);
        } catch (OutOfBoundsException $e) {
            return ValidationResult::error("events don't fit on chain, '%s' not found", $previous);
        }
        
        $next = reset($following);
        
        foreach ($newEvents->events as $event) {
            if ($next === false) {
                $handled = $this->handleNewEvent($event);
                $validation->add($handled, "event '$event->hash';");
            } elseif ($event->hash !== $next->hash) {
                $validation->addError("fork detected; conflict on '%s' and '%s'", $event->hash, $next->hash);
            }
            
            $next = next($following);
            
            if ($validation->failed()) {
                break;
            }
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
        $validation = $event->validate();
        
        if ($event->previous !== $this->chain->getLatestHash()) {
            $validation->addError("event '%s' doesn't fit on chain", $event->hash);
        }
        
        if ($validation->failed()) {
            return $validation;
        }
        
        $resource = $this->resourceManager->extractFrom($event);
        $this->addResource($resource, $event);
        
        $this->chain->events->add($event);
        
        return $validation;
    }
    
    /**
     * Add or update a resource
     * 
     * @param Resource $resource
     * @param Event    $event
     */
    public function addResource(Resource $resource, Event $event)
    {
        $identities = $this->chain->identities->filterOnSignkey($event->signkey);
        $privileges = array_filter($identities->getPrivileges($resource));
        
        if (empty($privileges)) {
            return; // Not allowed, so ignore
        }
        
        $resource->applyPrivileges($privileges);
        
        $this->resourceManager->store($resource);
        $this->chain->registerResource($resource);
    }
}
