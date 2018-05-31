<?php

use Psr\Container\ContainerInterface;

return [
    'config' => function (ContainerInterface $container) {
        return new AppConfig($container->get('app.env'));
    }
];