<?php

/**
 * Service to extract the resource from an event
 */
class ResourceFactory
{
    /**
     * Map schema to resource class
     * @var array 
     */
    protected $mapping = [
        'https://specs.livecontracts.io/v0.1.0/identity/schema.json#' => Identity::class,
        'https://specs.livecontracts.io/v0.1.0/template/schema.json#' => ExternalResource::class,
        'https://specs.livecontracts.io/v0.1.0/scenario/schema.json#' => ExternalResource::class,
        'https://specs.livecontracts.io/v0.1.0/form/schema.json#' => ExternalResource::class,
        'https://specs.livecontracts.io/v0.1.0/document/schema.json#' => ExternalResource::class,
        'https://specs.livecontracts.io/v0.1.0/response/schema.json#' => ResponseResource::class,
        'https://specs.livecontracts.io/v0.1.0/comment/schema.json#' => Comment::class
    ];
    
    
    /**
     * Class constructor
     * 
     * @param array $mapping  Map schema to resource class
     */
    public function __construct(array $mapping = null)
    {
        if (isset($mapping)) {
            array_walk($mapping, [$this, 'assertClassIsResource']);
            $this->mapping = $mapping;
        }
    }
    
    /**
     * Check that each class implements the Resource interface
     * 
     * @throws UnexpectedValueException
     */
    protected function assertClassIsResource($class)
    {
        if (!is_a($class, Resource::class, true)) {
            throw new UnexpectedValueException("$class is not a Resource");
        }
    }
    
    /**
     * Extract a resource from an event.
     * 
     * @param Event $event
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
            throw new UnexpectedValueException("Unrecognized schema '$schema' for event '$event->hash'");
        }
        
        $class = $this->mapping[$schema];
        
        return $class::fromEvent($event);
    }
}
