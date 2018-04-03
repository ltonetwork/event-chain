<?php

use Jasny\DB\BasicEntity;
use Jasny\DB\Entity\ChangeAware;
use Jasny\DB\Entity\Meta;
use Jasny\DB\Entity\Validation;
use Jasny\DB\FieldMapping;
use Jasny\DB\Mongo\Document\MetaImplementation;

/**
 * Embedded document in MongoDB
 */
class MongoSubDocument extends BasicEntity implements ChangeAware, Meta, Validation, FieldMapping
{
    use MetaImplementation,
        ChangeAware\Implementation
    {
        MetaImplementation::cast as private metaCast;
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->cast();
    }
    
    /**
     * @ignore
     * @return $this
     */
    public function expand()
    {
        return $this;
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
     * Cast all properties
     * 
     * @return $this
     */
    public function cast()
    {
        return $this->metaCast();
    }
    
    
    /**
     * Get the field map
     * 
     * @return array
     */
    public static function getFieldMap()
    {
        return [];
    }
}
