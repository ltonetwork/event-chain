<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Autowire\Autowire;
use Jasny\Autowire\ReflectionAutowire;
use Jasny\ReflectionFactory\ReflectionFactory;

return [
    Autowire::class => function(ContainerInterface $container) {
        $reflection = $container->get(ReflectionFactory::class);

        return new ReflectionAutowire($container, $reflection);
    }
];
