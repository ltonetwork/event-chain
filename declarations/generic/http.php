<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\HttpMessage\ServerRequest;
use Jasny\HttpMessage\Response;

return [
    ServerRequestInterface::class => function () {
        return (new ServerRequest())->withGlobalEnvironment();
    },
    ResponseInterface::class => function () {
        return new Response();
    }
];
