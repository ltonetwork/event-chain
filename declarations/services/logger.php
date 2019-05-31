<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Handler\PsrHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

return [
    LoggerInterface::class => function (ContainerInterface $container) {
        $rollbar = $container->get('rollbar.logger');

        $handler = isset($rollbar)
            ? new PsrHandler($rollbar)
            : new ErrorLogHandler();

        return new Logger('', [$handler]);
    },

    // Alias
    'logger' => function (ContainerInterface $container) {
        return $container->get(LoggerInterface::class);
    }
];
