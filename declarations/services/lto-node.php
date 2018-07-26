<?php

use Psr\Container\ContainerInterface;
use Jasny\Config;
use LTO\AccountFactory;

return [
    'node.config' => function(ContainerInterface $container) {
        return new Config('config/node.yml');
    },
    'node.account' => function(ContainerInterface $container) {
        /** @var $factory AccountFactory */
        $factory = $container->get(AccountFactory::class);
        $data = arrayify($container->get('node.config'));

        return $factory->create($data);
    }
];
