<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;
use Jasny\HttpDigest\HttpDigest;

return [
    ResourceFactory::class => function () {
        return new ResourceFactory();
    },
    ResourceMapping::class => function (ContainerInterface $container) {
        $endpoints = (array)$container->get('config.endpoints');

        return new ResourceMapping($endpoints);
    },
    ResourceStorage::class => function (AutowireContainerInterface $container) {
        $endpoints = (array)$container->get('config.endpoints');
        $httpClient = $container->get(GuzzleHttp\ClientInterface::class);
        $httpErrorWarning = $container->get(HttpErrorWarning::class);
        $digest = $container->get(HttpDigest::class);
        $node = $container->get('node.account');

        return new ResourceStorage($endpoints, $httpClient, $httpErrorWarning, $node, $digest);
    }
];
