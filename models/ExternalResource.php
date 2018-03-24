<?php

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Dynamic;

/**
 * A resource that is stored on an another system
 */
class ExternalResource implements Resource, Identifiable, Dynamic
{
    use ResourceBase;

    /**
     * JSON Schema
     * @var string
     */
    public $schema;
    
    /**
     * Identifier as URI
     * @var string
     */
    public $id;

    /**
     * Date/time the scenario was created
     * @var DateTime
     */
    public $timestamp;
    
    
    /**
     * Get the identifier
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the identifier
     * 
     * @return string
     */
    public static function getIdProperty()
    {
        return 'id';
    }
    
    /**
     * Set version by hashing the body
     * 
     * @param string $body  Base58 JSON encoded body
     */
    public function setVersionFrom($body)
    {
        $base58 = new \StephenHill\Base58();
        
        $hash = hash('sha256', $body, true);
        $version = substr($base58->encode($hash), 0, 8);
        
        $this->id = jasny\str_before($this->id, '?') . '?v=' . $version;
    }

    /**
     * Extract a resource from an event
     * 
     * @param Event $event
     * @return static
     */
    public static function fromEvent(Event $event)
    {
        $data = $event->getBody();
        
        $resource = new static();
        $resource->setValues([
            'schema' => $data['$schema'],
            'timestamp' => $event->timestamp
        ] + array_without($data, '$schema'));
        
        $resource->setVersionFrom($event->body);
    }
}
