<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;

return [
    EventFactory::class => function(ContainerInterface $container) {
        return new EventFactory($container->get('node.account'));
    },
    EventManager::class => function(AutowireContainerInterface $container) {
        return $container->autowire(EventManager::class);
    }
];
