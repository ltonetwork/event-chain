<?php

/**
 * EventChain entity
 */
class EventChain extends MongoDocument
{
    /**
     * Unique identifier
     * @var string
     */
    public $id;
    
    /**
     * List of event
     * @var Event[]|Jasny\DB\EntitySet
     */
    public $events = [];
    
    /**
     * Projected identities
     * @var Identity[]|IdentitySet
     */
    public $identities = [];

    /**
     * Resources that are part of this chain
     * @var array
     */
    public $resources = [];
    
    /**
     * Get the initial hash which is based on the event chain id
     */
    public function getInitialHash()
    {
        $base58 = new StephenHill\Base58();
        
        return $base58->encode(hash('sha256', $this->id, true));
    }
    
    /**
     * Get the first event of the chain.
     * 
     * @return Event
     * @throws UnderflowException
     */
    public function getFirstEvent()
    {
        if (count($this->events) === 0) {
            throw new UnderflowException("chain has no events");
        }
        
        return $this->events[0];
    }
    
    /**
     * Get the last event of the chain.
     * 
     * @return Event
     * @throws UnderflowException
     */
    public function getLastEvent()
    {
        if (count($this->events) === 0) {
            throw new UnderflowException("chain has no events");
        }
        
        return $this->events[count($this->events) - 1];
    }
    
    /**
     * Check if this chain has the genisis event or is empty
     * 
     * @return boolean
     */
    public function isPartial()
    {
        return count($this->events) > 0 && $this->getFirstEvent()->previous !== $this->getInitialHash();
    }
    
    /**
     * Check if id is valid
     * 
     * @return boolean
     */
    public function isValidId()
    {
        $firstEvent = $this->getFirstEvent();
        
        $base58 = new StephenHill\Base58();
        
        $signkey = $base58->decode($firstEvent->signkey);
        $signkeyHashed = substr(Keccak::hash(sodium\crypto_generichash($signkey, null, 32), 256), 0, 40);
        
        $decodedId = $base58->decode($this->id);
        $vars = unpack('Cversion/H16random/H40keyhash/H8checksum', $decodedId);
        
        return
            $vars['version'] === 1 &&
            $vars['keyhash'] === substr($signkeyHashed, 0, 40) &&
            $vars['checksum'] === substr(bin2hex($decodedId), -8);
    }
    
    /**
     * Validate the chain
     * 
     * @return \Jasny\ValidationResult
     */
    public function validate()
    {
        $validation = parent::validate();
        
        if (count($this->events) === 0) {
            $validation->addError('no events');
        } elseif ($this->getFirstEvent()->previous === $this->getInitialHash() && !$this->isValidId()) {
            $validation->addError('invalid id');
        }
        
        $validation->add($this->validateIntegrity());
        
        return $validation;
    }
    
    protected function validateIntegrity()
    {
        $validation = new Jasny\ValidationResult();
        $previous = null;
        
        foreach ($this->events as $event) {
            if (isset($previous) && $event->previous !== $previous) {
                $validation->addError(
                    "broken chain; previous of '%s' is '%s', expected '%s'",
                    $event->hash, $event->previous, $previous
                );
            }
            
            $previous = $event->hash;
        }
        
        return $validation;
    }
    
    /**
     * Return an event chain without any events
     * 
     * @return static
     */
    public function withoutEvents()
    {
        $emptyChain = new static();
        $emptyChain->id = $this->id;
        
        return $emptyChain;
    }
    
    /**
     * Get all events that follow the specified event.
     * 
     * @param string $hash
     * @return Event[]
     * @throws OutOfBoundsException if event can't be found
     */
    public function getEventsAfter($hash)
    {
        if ($this->getInitialHash() === $hash) {
            return $this->events->getArrayCopy();
        }
        
        $events = null;
        
        foreach ($this->events as $event) {
            if (isset($events)) {
                $events[] = $event;
            } elseif ($event->hash === $hash) {
                $events = [];
            }
        }
        
        if (!isset($events)) {
            throw new OutOfBoundsException("Event '$hash' not found");
        }
        
        return $events;
    }
}
