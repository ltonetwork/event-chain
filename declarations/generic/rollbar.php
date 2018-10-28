<?php

/**
 * @see https://github.com/rollbar/rollbar-php
 * @internal Uses the global scope. Do not use when running tests.
 */

use Psr\Container\ContainerInterface;
use Rollbar\Rollbar;

return [
    'rollbar.config' => function (ContainerInterface $container) {
        $config = $container->get('config');

        if (!isset($config->rollbar)) {
            return null;
        }

        $settings = [
            'code_version' => 'v' . self::version(),
            'environment' => self::env(null, false),
            'host' => preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'])
        ];

        return $settings + arrayify($config->rollbar);
    },
    'rollbar.logger' => function () {
        return Rollbar::logger();
    }
];