<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;

return [
    'models.anchor.client' => function (ContainerInterface $container) {
        $config = $container->get('config')->anchor;
        $httpClient = $container->get(ClientInterface::class);

        return new Anchor($config, $httpClient);
    }
];
