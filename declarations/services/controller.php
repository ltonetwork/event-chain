<?php

use Psr\Container\ContainerInterface;

return [
    'controller.factory' => function (ContainerInterface $container) {
        return new ControllerFactory(null, $container);
    }
];
