<?php declare(strict_types=1);

use Jasny\Container\AutowireContainerInterface;

return [
    AnchorClient::class => function (AutowireContainerInterface $container) {
        $config = isset($container->get('config')->anchor) ? $container->get('config')->anchor : false;
        $client = $config === false ?
            new NoAnchorClient() :
            $container->autowire(AnchorClient::class, $config);

        return $client;
    }
];
