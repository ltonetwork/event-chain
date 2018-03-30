<?php

use Jasny\HttpMessage\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Codeception\Util\Stub;
use Assetic\AssetWriter;

App::reset(); // Reset global state

$httpTriggerHistory = [];

// Overwrite the following container entries
$overwrite = [
    ServerRequestInterface::class => function() {
        return new ServerRequest();
    },
    LoggerInterface::class => function() {
        return new Logger('', [new TestHandler()]);
    },
    AssetWriter::class => function() {
        return Stub::make(AssetWriter::class, ['writeManagerAssets' => function () {}]);
    },
    'httpHistory' => function() use (&$httpTriggerHistory) {
        return $httpTriggerHistory;
    },
    GuzzleHttp\Handler\MockHandler::class => function() {
        return new GuzzleHttp\Handler\MockHandler();
    },
    GuzzleHttp\Client::class => function(ContainerInterface $container) use (&$httpTriggerHistory) {
        $mock = $container->get(GuzzleHttp\Handler\MockHandler::class);
        
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $handler->push(GuzzleHttp\Middleware::history($httpTriggerHistory));
        
        return new GuzzleHttp\Client(['handler' => $handler]);
    }
];

$container = new AppContainer($overwrite);

// Setup global state
App::setContainer($container);
App::sessionStart();

return $container;
