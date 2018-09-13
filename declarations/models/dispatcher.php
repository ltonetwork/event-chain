<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

return [
    'models.dispatcher.client' => function (ContainerInterface $container) {
        $config = $container->get('config')->dispatcher;
        $httpClient = $container->get(ClientInterface::class);

        return new Dispatcher($config, $httpClient);
    },
    'models.dispatcher.manager' => function (ContainerInterface $container) {
        $dispatcher = $container->get('models.dispatcher.client');
        $account = $container->get('node.account');
        $logger = $container->get(LoggerInterface::class);

        return new DispatcherManager($dispatcher, $account, $logger);
    }
];
