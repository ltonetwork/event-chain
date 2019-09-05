<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;

return [
    EventChainGateway::class => static function () {
        return new EventChainGateway();
    },
    EventChainReset::class => static function (AutowireContainerInterface $container) {
        return $container->autowire(EventChainReset::class);
    },
    EventChainRebase::class => static function (AutowireContainerInterface $container) {
        return $container->autowire(EventChainRebase::class);
    },
    ConflictResolver::class => static function (AutowireContainerInterface $container) {
        return $container->autowire(ConflictResolver::class);
    },

    // Alias
    'models.event_chains' => static function (ContainerInterface $container) {
        return $container->get(EventChainGateway::class);
    }
];
