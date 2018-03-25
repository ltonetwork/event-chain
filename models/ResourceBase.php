<?php

use Jasny\DB\Entity;

/**
 * Resource base class
 */
trait ResourceBase
{
    use Entity\Implementation,
        Entity\Redactable\Implementation
    {
        Entity\Implementation::setValues as private setValuesRaw;
        Entity\Implementation::getValues as private getUnredactedValues;
    }
    
    /**
     * JSONSchema uri
     * 
     * @var string
     */
    public $schema;
    
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
        
        return $this->setValuesRaw($values);
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
            $only = array_merge(['schema', 'id', 'timestamp', 'identity'], $privilege->only);
            $this->withOnly(...$only);
        }
        
        if (isset($privilege->not)) {
            $not = array_diff($privilege->not, ['schema', 'id', 'timestamp', 'identity']);
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
        // Do nothing
        
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
        $resource = new static();
        $resource->setValues($event->getBody());
        
        return $resource;
    }
}
