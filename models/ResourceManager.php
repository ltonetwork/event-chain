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
     */
    public function extractFrom(Event $event)
    {
        $body = $event->getBody();
        $schema = $body['$schema'];
        
        throw new UnexpectedValueException("Unrecognized schema '$schema' for event '$event->hash'");
    }
}
