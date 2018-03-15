<?php

use Jasny\HttpMessage\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Codeception\Util\Stub;
use Assetic\AssetWriter;

App::reset(); // Reset global state

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
    }
];

$container = new AppContainer($overwrite);

// Setup global state
App::setContainer($container);
App::sessionStart();

return $container;
