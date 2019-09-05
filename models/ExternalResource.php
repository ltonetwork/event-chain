<?php

declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Dynamic;
use function Jasny\str_before;

/**
 * A resource that is stored on an another system.
 */
class ExternalResource implements ResourceInterface, Identifiable, Dynamic
{
    use ResourceBase {
        ResourceBase::jsonSerialize as _jsonSerialize;
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

    /**
     * Cast to json
     * 
     * @return object
     */
    public function jsonSerialize(): object
    {
        $data = $this->_jsonSerialize();
        if (!isset($data->id)) {
            unset($data->id);
        } 

        if (isset($this->timestamp) && $this->timestamp instanceof DateTime) {
            $data->timestamp = $this->timestamp->getTimestamp();
        }

        return $data;
    }

    /**
     * Cast the external resource to data to be stored.
     *
     * @return array
     */
    public function toData(): array
    {
        return [
            'schema' => $this->schema,
            'id' => $this->id,
            'endpoints' => $this->endpoints,
        ];
    }
}
