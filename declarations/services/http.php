<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\HttpMessage\ServerRequest;
use Jasny\HttpMessage\Response;
use Jasny\HttpMessage\Emitter;
use Jasny\Container\AutowireContainerInterface;

return [
    ServerRequestInterface::class => function () {
        return (new ServerRequest())->withGlobalEnvironment();
    },
    ResponseInterface::class => function () {
        return new Response();
    },
    Emitter::class => function(AutowireContainerInterface $container) {
        return $container->autowire(Emitter::class);
    }
];
