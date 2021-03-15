<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

return [
    PDO::class => function (ContainerInterface $container) {
        $config = $container->get('config')->mysql;

        return new PDO(
            $config->dsn ?? '',
            $config->username ?? '',
            $config->password ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }
];
