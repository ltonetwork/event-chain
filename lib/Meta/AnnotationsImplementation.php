<?php

namespace Meta;

use ReflectionClass;

/**
 * Get class metadata through annotations
 *
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/meta/master/LICENSE MIT
 * @link    https://jasny.github.com/meta
 */
trait AnnotationsImplementation
{
    /**
     * Get metadata
     *
     * @return Jasny\Meta
     */
    public static function meta()
    {
        
        $factory = new AnnotationsFactory();
        
        $refl = new ReflectionClass(get_called_class());
        return $factory->create($refl);
    }
}
