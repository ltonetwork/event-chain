<?php declare(strict_types=1);

use Jasny\DB\Entity;
use Meta\AnnotationsImplementation;

/**
 * ResourceInterface base class
 */
trait ResourceBase
{
    use Entity\Implementation,
        Entity\Redactable\Implementation,
        Entity\Meta\Implementation,
        Entity\Validation\MetaImplementation,
        AnnotationsImplementation
    {
        Entity\Meta\Implementation::cast as private metaCast;
        Entity\Implementation::setValues as private setValuesRaw;
        Entity\Implementation::getValues as private getUnredactedValues;
        Entity\Implementation::jsonSerializeFilter insteadof Entity\Meta\Implementation;
        Meta\AnnotationsImplementation::meta insteadof Entity\Meta\Implementation;
    }
    
    /**
     * JSONSchema uri
     *
     * @var string
     */
    public $schema;
    
    /**
     * Date/time the (version of the) resource was created
     * @var DateTime
     */
    public $timestamp;
    
    
    /**
     * Cast properties
     * @codeCoverageIgnore
     *
     * @return $this
     */
    public function cast()
    {
        if (is_int($this->timestamp) || (is_string($this->timestamp) && ctype_digit($this->timestamp))) {
            $this->timestamp = DateTime::createFromFormat('U', (string)$this->timestamp);
        } elseif (is_string($this->timestamp) && strpos($this->timestamp, '(') !== false) { //Remove timezone in brackets, that can cause an error
            $this->timestamp = DateTime::createFromFormat('D M d Y H:i:s e+', $this->timestamp);
        }
        
        return $this->metaCast();
    }

    /**
     * Set the values of the resource
     *
     * @param array|object $values
     * @return $this
     */
    public function setValues($values)
    {
        if (is_object($values)) {
            $values = (array)$values;
        }
        
        if (isset($values['$schema'])) {
            $values['schema'] = $values['$schema'];
            unset($values['$schema']);
        }
        
        $this->setValuesRaw($values);
        $this->cast();
        
        return $this;
    }
    
    /**
     * Get (filtered) values of the resource
     *
     * @return array
     */
    public function getValues(): array
    {
        /** @var array $values  Bug in Jasny DB */
        $values = $this->getUnredactedValues();
        
        foreach (array_keys($values) as $property) {
            $censored = $this->hasMarkedAsCensored($property);
            
            if (!isset($censored)) {
                $censored = $this->isCensoredByDefault();
            }
            
            if ($censored) {
                unset($values[$property]);
            }
        }
        
        return $values;
    }

    /**
     * Get the resource JSONSchema declaration.
     *
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * Apply privilege, removing properties if needed.
     *
     * @param Privilege $privilege
     * @return $this
     */
    public function applyPrivilege(Privilege $privilege)
    {
        if (isset($privilege->only)) {
            $only = array_merge(['schema', 'id', 'event', 'timestamp', 'identity'], $privilege->only);
            $this->withOnly(...$only);
        }
        
        if (isset($privilege->not)) {
            $not = array_diff($privilege->not, ['schema', 'id', 'event', 'timestamp', 'identity']);
            $this->without(...$not);
        }
        
        return $this;
    }
    
    /**
     * Set the identity that created this (version of the) resource
     *
     * @param Identity $identity
     * @return $this
     */
    public function setIdentity(Identity $identity): self
    {
        if (property_exists($this, 'identity')) {
            $this->identity = $identity;
        }
        
        return $this;
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
            'timestamp' => $event->timestamp
        ] + $data);
        
        return $resource;
    }
    
    /**
     * JSON Serialize
     *
     * @return object
     */
    public function jsonSerializeFilterSchema(stdClass $object)
    {
        $object->{'$schema'} = $object->schema;
        unset($object->schema);
        
        return $object;
    }
}
