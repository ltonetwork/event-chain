<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;

return [
    EventChainGateway::class => function() {
        return new EventChainGateway();
    },
    EventChainRebase::class => function(AutowireContainerInterface $container) {
        return $container->autowire(EventChainRebase::class);
    },
    ConflictResolver::class => function(AutowireContainerInterface $container) {
        return $container->autowire(ConflictResolver::class);
    },

    // Alias
    'models.event_chains' => function (ContainerInterface $container) {
        return $container->get(EventChainGateway::class);
    }
];
