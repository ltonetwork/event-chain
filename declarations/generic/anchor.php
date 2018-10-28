<?php

use Jasny\Container\AutowireContainerInterface;

return [
    AnchorClient::class => function (AutowireContainerInterface $container) {
        $config = $container->get('config:anchor');
        $container->autowire(AnchorClient::class, $config);
    }
];
