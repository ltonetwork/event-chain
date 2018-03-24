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
        Entity\Implementation::getValues as private getUnredactedValues;
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
     */
    public function applyPrivilege(Privilege $privilege)
    {
        if (isset($privilege->only)) {
            $only = array_merge(['$schema', 'id', 'timestamp', 'identity'], $privilege->only);
            $this->withOnly(...$only);
        }
        
        if (isset($privilege->not)) {
            $this->without(...$privilege->not);
        }
    }
    
    /**
     * Set the identity that created this (version of the) resource
     * 
     * @param Identity $identity
     */
    public function setIdentity(Identity $identity)
    {
        // Do nothing
    }
    
    /**
     * Extract a resource from an event
     * 
     * @param \Event $event
     * @return static
     */
    public static function fromEvent(\Event $event)
    {
        $resource = new static();
        $resource->setValues($event->getBody());
    }
}
