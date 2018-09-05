<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;

return [
    'models.resources.factory' => function() {
        return new ResourceFactory();
    },
    'models.resources.storage' => function (ContainerInterface $container) {
        $config = $container->get('config');
        $httpClient = $container->get(ClientInterface::class);

        return new ResourceStorage(arrayify($config->endpoints), $httpClient);
    }
];
