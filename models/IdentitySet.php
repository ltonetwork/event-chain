<?php

use Jasny\DB\EntitySet;
use Jasny\DB\Entity\Identifiable;

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
        
        $filteredSet->entities = array_values(array_filter($filteredSet->entities, function($entity) use ($signkey) {
            return in_array($signkey, $entity->signkeys);
        }));
        
        return $filteredSet;
    }
    
    /**
     * Get all privileges for a resource.
     * 
     * @param Resource $resource
     * @return Privilege[]
     */
    public function getPrivileges(Resource $resource)
    {
        $schema = $resource->schema;
        $id = $resource instanceof Identifiable ? $resource->getId() : null;
        
        $privileges = [];
        
        foreach ($this->entities as $identity) {
            if (!isset($identity->privileges)) {
                $privileges = [ new Privilege() ];
                break;
            }
            
            foreach ($identity->privileges as $privilege) {
                if ($privilege->match($schema, $id)) {
                    $privileges[] = $privilege;
                }
            }
        }
        
        return $privileges;
    }
}
