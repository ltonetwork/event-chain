<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;

return [
    'lib:dispatcher' => function (ContainerInterface $container) {
        $config = $container->get('config');
        $httpClient = $container->get(ClientInterface::class);

        return new Dispatcher($config, $httpClient);
    }
];
