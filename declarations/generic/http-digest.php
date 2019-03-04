<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\HttpDigest\HttpDigest;
use Jasny\HttpDigest\ClientMiddleware;
use Jasny\HttpDigest\Negotiation\DigestNegotiator;

return [
    HttpDigest::class => function (ContainerInterface $container) {
        return new HttpDigest(new DigestNegotiator(), 'SHA-256');
    },
    ClientMiddleware::class => function(ContainerInterface $container) {
        $service = $container->get(HttpDigest::class);

        return new ClientMiddleware($service);
    }
];
