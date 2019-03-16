<?php declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Dynamic;
use function Jasny\str_before;

/**
 * A resource that is stored on an another system
 */
class ExternalResource implements ResourceInterface, Identifiable, Dynamic
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
     * The identity that created the (version of the) resource
     * @var Identity
     */
    public $identity;    
    
    /**
     * Get the identifier
     *
     * @return string|null
     */
    public function getId(): ?string
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
}
