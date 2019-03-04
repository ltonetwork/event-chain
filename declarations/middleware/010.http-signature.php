<?php declare(strict_types=1);

use Jasny\RouterInterface;
use Jasny\HttpDigest\HttpDigest;
use Jasny\HttpDigest\ServerMiddleware as HttpDigestMiddleware;
use Jasny\HttpSignature\HttpSignature;
use Jasny\HttpSignature\ServerMiddleware as HttpSignatureMiddleware;
use Psr\Container\ContainerInterface;
use LTO\Account\ServerMiddleware as AccountMiddleware;

return [
    static function (RouterInterface $router, ContainerInterface $container) {
        $service = $container->get(HttpDigest::class);
        $middleware = new HttpDigestMiddleware($service);

        return $middleware->asDoublePass();
    },
    static function (RouterInterface $router, ContainerInterface $container) {
        $service = $container->get(HttpSignature::class);
        $middleware = new HttpSignatureMiddleware($service);

        return $middleware->asDoublePass();
    },
    static function (RouterInterface $router, ContainerInterface $container) {
        $accountFactory = $container->get(AccountFactory::class);
        $middleware = new AccountMiddleware($accountFactory);

        return $middleware->asDoublePass();
    },
];
