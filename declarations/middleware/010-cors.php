<?php

declare(strict_types=1);

use Jasny\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return [
    static function (RouterInterface $router, ContainerInterface $container) {
        return function(Request $request, Response $baseResponse, callable $next): Response {
            $response = $request->getMethod() === 'OPTIONS'
                ? $baseResponse->withStatus(200)
                : $next($request, $baseResponse);

            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', '*')
                ->withHeader('Access-Control-Allow-Headers', '*')
                ->withHeader('Access-Control-Max-Age', 1728000);  // 20 days
        };
    },
];
