<?php

use Jasny\DB\Mongo;
use Jasny\DB\Entity\Enrichable;
use Jasny\DB\Mongo\Document\SoftDeletion;

/**
 * Base class for Mongo Documents
 */
abstract class MongoDocument extends Mongo\Document implements 
    Enrichable,
    SoftDeletion
{
    use Enrichable\Implementation,
        SoftDeletion\FlagImplementation;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->cast();
    }
    
    /**
     * Cast properties
     * 
     * @return $this
     */
    public function cast()
    {
        return parent::cast();
    }
    
    /**
     * Get the unmodified value of a field
     * 
     * @param string $property
     * @return mixed
     */
    public function getOriginal($property)
    {
        $map = static::mapToFields([$property => null]);
        $field = key($map);
        
        return $this->getPersistedData()[$field];
    }

    /**
     * Create entity
     * 
     * @return static
     */
    public static function create(...$args)
    {
        return new static(...$args);
    }
    
    /**
     * Get the persisted value of a field
     * 
     * @param string $property
     * @return mixed
     */
    final public function getPersisted($property)
    {
        return $this->getOriginal($property);
    }

    /**
     * Check if a string is a valid MongoId
     * 
     * @param string $id
     * @return boolean
     */
    public static function isValidMongoId($id)
    {
        return $id instanceof MongoId || (is_string($id) && strlen($id) === 24 && ctype_xdigit($id));
    }    
}
