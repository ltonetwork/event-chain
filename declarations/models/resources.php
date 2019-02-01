<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;

return [
    ResourceFactory::class => function () {
        return new ResourceFactory();
    },
    ResourceMapping::class => function (ContainerInterface $container) {
        $endpoints = (array)$container->get('config.endpoints');

        return new ResourceMapping($endpoints);
    },
    ResourceStorage::class => function (AutowireContainerInterface $container) {
        return $container->autowire(ResourceStorage::class);
    }
];
