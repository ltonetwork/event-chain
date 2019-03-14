<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Jasny\HttpDigest\ClientMiddleware as HttpDigestMiddleware;
use Jasny\HttpSignature\ClientMiddleware as HttpSignatureMiddleware;

return [
    HandlerStack::class => function(ContainerInterface $container) {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $digestMiddleware = $container->get(HttpDigestMiddleware::class);
        $stack->push($digestMiddleware->forGuzzle());

        $signatureMiddleware = $container->get(HttpSignatureMiddleware::class);
        $stack->push($signatureMiddleware->forGuzzle());

        return $stack;
    },
    ClientInterface::class => function (ContainerInterface $container) {
        $stack = $container->get(HandlerStack::class);

        return new Client(['handler' => $stack, 'timeout' => 20]);
    }
];
