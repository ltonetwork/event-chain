<?php

use Jasny\Container\AutowireContainerInterface;
use Jasny\Router\ControllerFactory;

return [
    ControllerFactory::class => function (AutowireContainerInterface $container) {
        return new ControllerFactory(function(string $controllerClass) use ($container) {
            return $container->autowire($controllerClass);
        });
    }
];
