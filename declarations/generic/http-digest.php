<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\HttpDigest\HttpDigest;
use Jasny\HttpDigest\ClientMiddleware;

return [
    HttpDigest::class => static function () {
        return new HttpDigest('SHA-256');
    },
    ClientMiddleware::class => static function(ContainerInterface $container) {
        $service = $container->get(HttpDigest::class);

        return new ClientMiddleware($service);
    }
];
