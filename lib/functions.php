<?php

use Improved as i;
use function Jasny\object_get_properties;

/**
 * Check if link to schema specification is valid
 * @param  string  $link
 * @param  string  $type
 * @return boolean
 */
function is_schema_link_valid(string $link, string $type)
{
    $pattern = '|https://specs\.livecontracts\.io/v\d+\.\d+\.\d+/' . preg_quote($type) . '/schema\.json#|';

    return (bool)preg_match($pattern, $link);
}

/**
 * Set the dependencies of an object.
 * This set private and protected properties.
 *
 * @param object $object
 * @param array $dependencies
 */
function object_set_dependencies(object $object, array $dependencies): void
{
    $fn = function($value, $key) {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
    };

    array_walk($dependencies, $fn->bindTo($object, $object));
}
