<?php

use Jasny\DB\EntitySet;

/**
 * Set of identities
 */
class IdentitySet extends EntitySet
{
    /**
     * Add an identity
     * 
     * @param Identity $entity
     */
    public function add(Identity $entity)
    {
        $existing = $this->get($entity);
        
        if (isset($existing)) {
            $existing->setValues($entity->getValues());
            return;
        }
        
        parent::add($entity);
    }
    
    
    /**
     * Get an identity by id
     * 
     * @param string $uri  An identity ID or signkey URI
     * @return Identity|null
     */
    public function get($uri)
    {
        $id = Jasny\str_before($uri, '#');
        
        return parent::get($id);
    }
}
