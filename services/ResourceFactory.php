<?php declare(strict_types=1);

/**
 * Service to extract the resource from an event
 */
class ResourceFactory
{
    /**
     * Map schema to resource class
     * @var array<string,string>
     */
    protected $mapping = [
        'https://specs.livecontracts.io/v0.2.0/identity/schema.json#' => Identity::class
    ];

    /**
     * Class constructor
     *
     * @param array|null $mapping  Map schema to resource class
     */
    public function __construct(?array $mapping = null)
    {
        if (isset($mapping)) {
            array_walk($mapping, [$this, 'assertClassIsResource']);
            $this->mapping = $mapping;
        }
    }

    /**
     * Check that each class implements the ResourceInterface interface
     *
     * @param string $class
     * @throws UnexpectedValueException
     */
    protected function assertClassIsResource(string $class): void
    {
        if (!is_a($class, ResourceInterface::class, true)) {
            throw new UnexpectedValueException("$class is not a ResourceInterface");
        }
    }

    /**
     * Extract a resource from an event.
     *
     * @param Event $event
     * @return ResourceInterface
     */
    public function extractFrom(Event $event): ResourceInterface
    {
        $body = $event->getBody();
        
        if (!isset($body['$schema'])) {
            throw new UnexpectedValueException("Invalid body; no schema for event '{$event->hash}'");
        }
        
        $schema = $body['$schema'];
        $class = isset($this->mapping[$schema]) ? $this->mapping[$schema] : ExternalResource::class;
        
        return $class::fromEvent($event);
    }
}
