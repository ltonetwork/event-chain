<?php declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Dynamic;
use function Jasny\str_before;

/**
 * A resource that is stored on an another system
 */
class ExternalResource implements ResourceInterface, Identifiable, Dynamic
{
    use ResourceBase {
        fromEvent as private fromEventBase;
    }

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
     * The identity that created the (version of the) resource
     * @var Identity
     */
    public $identity;
    
    
    /**
     * Get the identifier
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * Get the identifier
     *
     * @return string
     */
    public static function getIdProperty(): string
    {
        return 'id';
    }
    
    /**
     * Set version by hashing the body
     *
     * @param string $body  Base58 JSON encoded body
     * @return $this
     */
    public function setVersionFrom(string $body): self
    {
        $hash = hash('sha256', $body, true);
        $version = substr(base58_encode($hash), 0, 8);
        
        $this->id = str_before($this->id, '?') . '?v=' . $version;
        
        return $this;
    }

    /**
     * Extract a resource from an event
     *
     * @param Event $event
     * @return static
     */
    public static function fromEvent(Event $event): self
    {
        $resource = self::fromEventBase($event);
        $resource->setVersionFrom($event->body);
        
        return $resource;
    }
}
