<?php

use Jasny\DB\EntitySet;

/**
 * Set of identities
 */
class IdentitySet extends EntitySet
{
    /**
     * Add or update an identity
     * 
     * @param Identity $entity
     */
    public function set(Identity $entity)
    {
        $existing = $this->get($entity);
        
        if (isset($existing)) {
            $existing->setValues($entity->getValues());
            return;
        }
        
        $this->add($entity);
    }
    
    /**
     * Get a set with only identities that have specified signkey
     * 
     * @param string $signkey
     * @return static
     */
    public function filterOnSignkey($signkey)
    {
        $filteredSet = clone $this;
        $filteredSet->flags = $filteredSet->flags & ~static::ALLOW_DUPLICATES;
        
        $filteredSet->entities = array_filter($filteredSet->entities, function($entity) use ($signkey) {
            return in_array($signkey, $entity->signkeys);
        });
    }
    
    /**
     * Get all privileges for a resource and combine them.
     * 
     * @param Resource $resource
     * @return Privilege
     */
    public function getPrivilege(Resource $resource)
    {
        
    }
}
