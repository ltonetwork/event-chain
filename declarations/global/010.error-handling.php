<?php

/**
 * Initialize how global errors are being handled
 */

use Jasny\ErrorHandler;
use Jasny\ErrorHandlerInterface;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

$container = App::getContainer();
$config = $container->get('config');

if (!empty($config->debug)) {
    error_reporting(E_ALL & ~E_STRICT);

    $display_errors = isset($config->display_errors)
        ? $config->display_errors
        : (isset($_SERVER['HTTP_X_DISPLAY_ERRORS']) ? $_SERVER['HTTP_X_DISPLAY_ERRORS'] : null);

    if (isset($display_errors)) {
        ini_set('display_errors', $display_errors);
    }
} else {
    ini_set('display_error', false);
    error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT);
}

/* @var $logger Logger */
$logger = $container->get('logger');

$stdlog = array_reduce($logger->getHandlers(), function($found, $handler) {
    return $found || $handler instanceof ErrorLogHandler;
}, false);

if (!ini_get('display_errors') && !$stdlog) {
    $errorHandler = $container->get(ErrorHandlerInterface::class);

    $errorHandler->setLogger($logger);

    if ($errorHandler instanceof ErrorHandler) {
        $errorHandler->converErrorsToExceptions();
        $errorHandler->logUncaught(E_ALL);
    }
}
