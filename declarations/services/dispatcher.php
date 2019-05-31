<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

return [
    Dispatcher::class => function (ContainerInterface $container) {
        $config = isset($container->get('config')->queuer) ? $container->get('config')->queuer : false;
        $httpClient = $container->get(ClientInterface::class);

        $dispatcher = $config === false ?
            new NoDispatcher() :
            new Dispatcher($config, $httpClient);                        

        return $dispatcher;
    },
    DispatcherManager::class => function (ContainerInterface $container) {
        $dispatcher = $container->get(Dispatcher::class);
        $account = $container->get('node.account');
        $logger = $container->get(LoggerInterface::class);

        return new DispatcherManager($dispatcher, $account, $logger);
    }
];
