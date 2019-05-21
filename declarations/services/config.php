<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;

return [
    'config' => function (ContainerInterface $container) {
        return (new AppConfig)->load($container->get('app.env'));
    }
];
