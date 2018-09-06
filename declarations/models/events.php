<?php

use Psr\Container\ContainerInterface;

return [
    'models.events.factory' => function(ContainerInterface $container) {
        $account = $container->get('node.account');
        return new EventFactory($account);
    }
];
