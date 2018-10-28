<?php declare(strict_types=1);

/**
 * @see https://github.com/rollbar/rollbar-php
 * @internal Uses the global scope. Do not use when running tests.
 */

use Jasny\ApplicationEnv;
use Psr\Container\ContainerInterface;
use Rollbar\Rollbar;

return [
    'rollbar.config' => function (ContainerInterface $container) {
        $config = $container->get('config');
        $appEnv = $container->get(ApplicationEnv::class);

        if (!isset($config->rollbar)) {
            return null;
        }

        $settings = [
            'environment' => (string)$appEnv,
            'host' => preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'])
        ];

        return $settings + arrayify($config->rollbar);
    },
    'rollbar.logger' => function () {
        return Rollbar::logger();
    }
];
