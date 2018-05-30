<?php

return [
    'node.config' => function(ContainerInterface $container) {
        return new Jasny\Config('config/node.yml');
    },
    'node.account' => function(ContainerInterface $container) {
        $data = Jasny\arrayify($container->get('node.config'));

        $factory = $container->get(AccountFactory::class);

        return $factory->create($data);
    }
];