<?php

use Jasny\ValidationResult;

/**
 * Handle new events
 */
class EventManger
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
        $this->resourceManager = $resourceManager ?: new ResourceManager();
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
            return ValidationResult::error("events don't fit on chain, %s not found", $previous);
        }
        
        foreach ($newEvents->events as $event) {
            $next = next($following);
            
            if ($next === false) {
                $handled = $this->handleNewEvent($event);
                $validation->add($handled, "event '$event->hash';");
            } elseif ($event->hash !== $next) {
                $validation->addError("fork detected; conflict on %s and %s", $event->hash, $next);
            }
            
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
        if ($this->chain->getLastEvent()->hash !== $event->previous) {
            $validation->addError("event %s doesn't fit on chain", $event->hash);
        }
        
        if ($validation->failed()) {
            return $validation;
        }
        
        $body = $event->getBody();
        
        $resource = $this->resourceManager->extractFrom($event);
        
        if ($resource instanceof Identity) {
            $this->addIdentity(Identity::create()->setValues($body), $event);
        } else {
            $this->resourceManager->store($resource);
        }
        
        $this->events->add($event);
        
        return $validation;
    }
    
    /**
     * Add a new identity to the chain
     * 
     * @param Identity $identity
     * @param Event $event
     * @return type
     */
    public function addIdentity(Identity $identity, Event $event)
    {
        if (count($this->chain->events) > 0) {
            $eventIdentity = $this->chain->getIdentity($event->signkey);
            $privilege = $eventIdentity->getPrivilege($identity);

            if (!$privilege) {
                return; // Not allowed to add / edit identity
            }

            if (isset($privilege->only)) {
                $identity->withOnly(...$privilege->only);
            } elseif (isset($privilege->not)) {
                $identity->without(...$privilege->not);
            }
        }
        
        $this->chain->addIdentity($identity);
    }
}
