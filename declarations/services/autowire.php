<?php

use Psr\Container\ContainerInterface;
use Jasny\Autowire\AutowireInterface;
use Jasny\Autowire\ReflectionAutowire;
use Jasny\ReflectionFactory\ReflectionFactory;

return [
    AutowireInterface::class => function(ContainerInterface $container) {
        $reflection = $container->get(ReflectionFactory::class);

        return new ReflectionAutowire($container, $reflection);
    }
];
