<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;

return [
    'models.dispatcher.client' => function (ContainerInterface $container) {
        $config = $container->get('config')->dispatcher;
        $httpClient = $container->get(ClientInterface::class);

        return new Dispatcher($config, $httpClient);
    }
];
