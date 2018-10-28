<?php declare(strict_types=1);

use Jasny\ReflectionFactory\ReflectionFactory;

return [
    ReflectionFactory::class => function() {
        return new ReflectionFactory();
    }
];
