<?php declare(strict_types=1);
/**
 * Environment variables
 */

use Jasny\ApplicationEnv;
use Psr\Container\ContainerInterface;

return [
    ApplicationEnv::class => function () {
        return new ApplicationEnv(getenv('APPLICATION_ENV') ?: 'dev');
    },
    'app.env' => function(ContainerInterface $container) {
        return $container->get(ApplicationEnv::class);
    }
];
