<?php declare(strict_types=1);

use Jasny\Container\AutowireContainerInterface;

return [
    AnchorClient::class => function (AutowireContainerInterface $container) {
        return $container->autowire(AnchorClient::class, $container->get('config.anchor'));
    }
];
