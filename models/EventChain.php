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
     * Get the genesis event of the chain
     * 
     * @return Event
     */
    public function getGenesisEvent()
    {
        if (!isset($this->events[0])) {
            throw new BadMethodCallException("chain has no events");
        }
        
        return $this->events[0];
    }
    
    /**
     * Check if id is valid
     * 
     * @return boolean
     */
    public function isValidId()
    {
        $genesisEvent = $this->getGenesisEvent();
        
        $base58 = new StephenHill\Base58();
        
        $signkey = $base58->decode($genesisEvent->signkey);
        $signkeyHashed = substr(Keccak::hash(sodium\crypto_generichash($signkey, null, 32), 256), 0, 40);
        
        $decodedId = $base58->decode($this->id);
        $vars = unpack('Cversion/H16random/H40keyhash/H8checksum', $decodedId);
        
        return
            $vars['version'] === 1 &&
            $vars['keyhash'] === substr($signkeyHashed, 0, 40) &&
            $vars['checksum'] === substr(bin2hex($decodedId), -8);
    }
    
    /**
     * @inheritDoc
     */
    public function validate()
    {
        $validation = parent::validate();
        
        if (count($this->events) === 0) {
            $validation->addError('no events');
        } elseif (!$this->isValidId()) {
            $validation->addError('invalid id');
        }
        
        foreach ($this->events as $event) {
            $validation->add($event->validate(), 'event ' . $event->hash);
        }
        
        return $validation;
    }
}
