<?php

use Jasny\ReflectionFactory\ReflectionFactory;

return [
    ReflectionFactory::class => function() {
        return new ReflectionFactory();
    }
];
