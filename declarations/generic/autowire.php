<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Autowire\Autowire;
use Jasny\Autowire\AutowireInterface;
use Jasny\Autowire\ReflectionAutowire;
use Jasny\ReflectionFactory\ReflectionFactory;

return [
    Autowire::class => function (ContainerInterface $container) {
        return new ReflectionAutowire(
            $container,
            $container->get(ReflectionFactory::class)
        );
    },

    // Alias for BC
    AutowireInterface::class => function (ContainerInterface $container) {
        return $container->get(Autowire::class);
    }
];
