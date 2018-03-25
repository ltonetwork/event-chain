<?php

use Jasny\DB\Entity;

/**
 * Resource base class
 */
trait ResourceBase
{
    use Entity\Implementation,
        Entity\Redactable\Implementation,
        Entity\Meta\Implementation
    {
        Entity\Implementation::setValues as private setValuesRaw;
        Entity\Implementation::getValues as private getUnredactedValues;
        Entity\Implementation::jsonSerializeFilter insteadof Entity\Meta\Implementation;
    }
    
    /**
     * JSONSchema uri
     * 
     * @var string
     */
    public $schema;
    
    /**
     * The hash of the event
     * @var string 
     */
    public $event;
    
    /**
     * Date/time the (version of the) resource was created
     * @var DateTime
     */
    public $timestamp;
    
    
    /**
     * Set the values of the resource
     * 
     * @param array|object $values
     * @return $this
     */
    public function setValues($values)
    {
        if (is_object($values)) {
            $values = (array)$values;
        }
        
        if (isset($values['$schema'])) {
            $values['schema'] = $values['$schema'];
            unset($values['$schema']);
        }
        
        $this->setValuesRaw($values);
        $this->cast();
        
        return $this;
    }
    
    /**
     * Get (filtered) values of the resource
     * 
     * @return array
     */
    public function getValues()
    {
        $values = $this->getUnredactedValues();
        
        foreach (array_keys($values) as $property) {
            $censored = $this->hasMarkedAsCensored($property);
            
            if (!isset($censored)) {
                $censored = $this->isCensoredByDefault();
            }
            
            if ($censored) {
                unset($values[$property]);
            }
        }
        
        return $values;
    }
    
    /**
     * Apply privilege, removing properties if needed.
     * 
     * @param Privilege $privilege
     * @return $this
     */
    public function applyPrivilege(Privilege $privilege)
    {
        if (isset($privilege->only)) {
            $only = array_merge(['schema', 'id', 'event', 'timestamp', 'identity'], $privilege->only);
            $this->withOnly(...$only);
        }
        
        if (isset($privilege->not)) {
            $not = array_diff($privilege->not, ['schema', 'id', 'event', 'timestamp', 'identity']);
            $this->without(...$not);
        }
        
        return $this;
    }
    
    /**
     * Set the identity that created this (version of the) resource
     * 
     * @param Identity $identity
     * @return $this
     */
    public function setIdentity(Identity $identity)
    {
        if (property_exists(get_class($this), 'identity')) {
            $this->identity = $identity;
        }
        
        return $this;
    }

    /**
     * Extract a resource from an event
     * 
     * @param Event $event
     * @return static
     */
    public static function fromEvent(Event $event)
    {
        $data = $event->getBody();
        
        $resource = new static();
        $resource->setValues([
            'event' => $event->hash,
            'timestamp' => $event->timestamp
        ] + $data);
        
        return $resource;
    }
}
