<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;

return [
    EventFactory::class => function(ContainerInterface $container) {
        $account = $container->get('node.account');
        return new EventFactory($account);
    },
    EventManager::class => function(AutowireContainerInterface $container) {
        $container->autowire(EventFactory::class);
    }
];
