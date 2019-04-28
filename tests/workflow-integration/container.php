<?php

use Jasny\HttpMessage\ServerRequest;
use Jasny\Container\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

$container = new Container(App::getContainerEntries());

// Setup global state
App::setContainer($container);

Jasny\DB::resetGlobalState();
Jasny\DB::configure($container->get('config.db'));

return $container;
