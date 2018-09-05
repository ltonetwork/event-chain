<?php

use Jasny\HttpMessage\ServerRequest;
use Jasny\Container\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

$httpTriggerHistory = [];

// Overwrite the following container entries
$overwrite = [
    ServerRequestInterface::class => function() {
        return new ServerRequest();
    },
    LoggerInterface::class => function() {
        return new Logger('', [new TestHandler()]);
    },
    'http.history' => function() use (&$httpTriggerHistory) {
        return $httpTriggerHistory;
    },
    GuzzleHttp\Handler\MockHandler::class => function() {
        return new GuzzleHttp\Handler\MockHandler();
    },
    GuzzleHttp\ClientInterface::class => function(ContainerInterface $container) use (&$httpTriggerHistory) {
        $mock = $container->get(GuzzleHttp\Handler\MockHandler::class);
        
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $handler->push(GuzzleHttp\Middleware::history($httpTriggerHistory));
        
        return new GuzzleHttp\Client(['handler' => $handler]);
    }
];

$entries = new AppendIterator();
$entries->append(App::getContainerEntries());
$entries->append(new ArrayIterator($overwrite));

$container = new Container($entries);

// Setup global state
App::setContainer($container);

return $container;
