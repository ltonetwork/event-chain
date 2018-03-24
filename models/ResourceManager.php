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
    public $mapping = [
        'http://specs.livecontracts.io/draft-01/02-identity/schema.json#' => Identity::class,
        'http://specs.livecontracts.io/draft-01/03-template/schema.json#' => ExternalResource::class,
        'http://specs.livecontracts.io/draft-01/04-scenario/schema.json#' => ExternalResource::class,
        'http://specs.livecontracts.io/draft-01/08-form/schema.json#' => ExternalResource::class,
        'http://specs.livecontracts.io/draft-01/10-document/schema.json#' => ExternalResource::class,
        'http://specs.livecontracts.io/draft-01/12-response/schema.json#' => ExternalResource::class,
        'http://specs.livecontracts.io/draft-01/13-comment/schema.json#' => Comment::class
    ];
    
    /**
     * Class constructor
     * 
     * @param array $mapping  Map schema to resource class
     */
    public function __construct(array $mapping = null)
    {
        if (isset($mapping)) {
            $this->mapping = $mapping;
        }
    }
    
    /**
     * Extract a resource from an event.
     * 
     * @param Event       $event
     * @return Resource
     */
    public function extractFrom(Event $event)
    {
        $body = $event->getBody();
        
        if (!isset($body['$schema'])) {
            throw new UnexpectedValueException("Invalid body; no schema for event '{$event->hash}'");
        }
        
        $schema = $body['$schema'];
        
        if (!isset($this->mapping[$schema])) {
            trigger_error("Unrecognized schema '$schema' for event '$event->hash'", E_USER_WARNING);
            return;
        }
        
        $class = $this->mapping[$schema];
        
        return $class::fromEvent($event);
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
