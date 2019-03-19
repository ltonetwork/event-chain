<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;
use Jasny\HttpDigest\HttpDigest;

return [
    ResourceFactory::class => function () {
        return new ResourceFactory();
    },
    ResourceStorage::class => function (AutowireContainerInterface $container) {
        $endpoints = (array)$container->get('config.endpoints');

        return $container->autowire(ResourceStorage::class, $endpoints);
    },
    ResourceStorage::class => function (AutowireContainerInterface $container) {
        $triggers = (array)$container->get('config.triggers');

        return $container->autowire(ResourceTrigger::class, $triggers);
    }
];
