<?php

/**
 * Service to extract the resource from an event
 */
class ResourceManager
{
    /**
     * Map schema to resource class
     * @var array 
     */
    public static $mapping = [];
    
    /**
     * Extract a resource from an event
     * 
     * @param Event $event
     * @return Resource
     */
    public function extractFrom(Event $event)
    {
        $body = $event->getBody();
        $schema = $body['$schema'];
        
        throw new UnexpectedValueException("Unrecognized schema '$schema' for event '$event->hash'");
    }
    
    public function applyPrivilege(Resource $resource, Privilege $privilege)
    {
        if (count($this->chain->events) > 0) {

            if (!$privilege) {
                return; // Not allowed to add / edit identity
            }

            if (isset($privilege->only)) {
                $identity->withOnly(...$privilege->only);
            } elseif (isset($privilege->not)) {
                $identity->without(...$privilege->not);
            }
        }
    }
    
    /**
     * Store a resource (on an external system)
     * 
     * @param Resource $resource
     */
    public function store(Resource $resource)
    {
        
    }
}
