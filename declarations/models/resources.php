<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;

return [
    ResourceFactory::class => function() {
        return new ResourceFactory();
    },
    ResourceStorage::class => function (ContainerInterface $container) {
        $config = $container->get('config');
        $httpClient = $container->get(ClientInterface::class);

        return new ResourceStorage(arrayify($config->endpoints), $httpClient);
    }
];
