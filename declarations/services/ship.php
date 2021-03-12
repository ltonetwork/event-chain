<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

return [
    ShipSync::class => function (ContainerInterface $container) {
        $mysql = $container->get(PDO::class);
        $node = $container->get('config.lto.node');
        $prefix = $container->get('config.ship.prefix');

        return new ShipSync($mysql, $prefix, $node);
    },
    ShipStore::class => function (ContainerInterface $container) {
        $mysql = $container->get(PDO::class);

        return new ShipStore($mysql);
    }
];
