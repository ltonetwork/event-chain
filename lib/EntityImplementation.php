<?php declare(strict_types=1);

use Jasny\DB\Entity;

/**
 * Fixing bugs in Jasny\DB\Entity\Implementation
 */
trait EntityImplementation
{    
    /**
     * Convert loaded values to an entity.
     * Calls the construtor *after* setting the properties.
     * 
     * @param array|stdClass $values
     * @return static
     */
    public static function fromData($values)
    {
        if (!is_array($values) && !$values instanceof stdClass) {
            $type = (is_object($values) ? get_class($values) . ' ' : '') . gettype($values);
            throw new \InvalidArgumentException("Expected an array or stdClass object, but got a $type");
        }

        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        $entity->setValues($values);
        if (method_exists($entity, '__construct')) {
            $entity->__construct();
        }
        
        return $entity;
    }
}
