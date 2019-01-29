<?php

namespace Meta;

use Reflector;
use ReflectionProperty;
use ReflectionMethod;
use InvalidArgumentException;

/**
 * Factory to create Meta from annotations
 */
class AnnotationsFactory extends \Jasny\Meta\Factory\Annotations
{    
    /**
     * Clean/Normalize var annotation gotten through reflection
     *
     * @important Fixes bug in Jasny\Meta\Factory\Annotations with DateTime global typing
     * @param ReflectionProperty|ReflectionMethod $refl
     * @param string                              $var
     * @return string
     */
    protected function normalizeVar(Reflector $refl, $var)
    {
        if (!$refl instanceof ReflectionProperty && !$refl instanceof ReflectionMethod) {
            throw new InvalidArgumentException("Unsupported Reflector class: " . get_class($refl));
        }
        
        if (strstr($var, '|')) {
            $vars = explode('|', $var);
            return join('|', array_map(function ($subvar) use ($refl) {
                return $this->normalizeVar($refl, $subvar);
            }, $vars));
        }
        
        // Remove additional var info
        if (strpos($var, ' ') !== false) $var = substr($var, 0, strpos($var, ' '));

        // Normalize call types to global namespace
        $internalTypes = ['bool', 'boolean', 'int', 'integer', 'float', 'string', 'array', 'object', 'resource',
            'mixed', 'self', 'static', '$this', 'DateTime'];

        if (!isset($var) || in_array($var, $internalTypes)) {
            return $var;
        }
        
        if ($var[0] === '\\') {
            $var = substr($var, 1);
        } else {
            $ns = $refl->getDeclaringClass()->getNamespaceName();
            if ($ns) $var = $ns . '\\' . $var;
        }
        
        return $var;
    }
}
