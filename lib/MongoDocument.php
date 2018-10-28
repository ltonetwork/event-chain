<?php declare(strict_types=1);

use Jasny\DB\Mongo;
use Jasny\DB\Entity\Enrichable;
use MongoDB\BSON\ObjectId;

/**
 * Base class for Mongo Documents.
 * @deprecated To be replaced with the new Jasny DB layer.
 */
abstract class MongoDocument extends Mongo\Document implements Enrichable
{
    use Enrichable\Implementation;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
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
     * @param mixed ...$args
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
     * @param ObjectId|string $id
     * @return boolean
     */
    public static function isValidMongoId($id)
    {
        return $id instanceof ObjectId || (is_string($id) && strlen($id) === 24 && ctype_xdigit($id));
    }

    /**
     * Cast to data.
     * Overwrite to support snapshot.
     *
     * @return array
     */
    public function toData()
    {
        $values = call_user_func('get_object_vars', $this); // `call_user_func` is used to only get public properties
        $meta = static::meta();

        foreach ($values as $property => &$item) {
            if ($item instanceof Entity\Identifiable && !$meta->ofProperty($property)['snapshot']) {
                $item = static::childEntityToId($item);
            } elseif (
                $item instanceof EntitySet &&
                is_a($item->getEntityClass(), Entity\Identifiable::class, true) &&
                !$meta->ofProperty($property)['snapshot']
            ) {
                $item = array_map(function($child) {
                    return static::childEntityToId($child);
                }, $item);
            } elseif ($item instanceof Data) {
                $item = $item->toData();
            }
        }

        if ($this instanceof Sorted && method_exists($this, 'prepareDataForSort')) {
            $values += static::prepareDataForSort($this);
        }
        $casted = static::castForDB($values);
        $data = static::mapToFields($casted);

        if (array_key_exists('_id', $data) && is_null($data['_id'])) {
            unset($data['_id']);
        }

        return $data;
    }
}
