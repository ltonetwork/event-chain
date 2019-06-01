<?php

declare(strict_types=1);

use Jasny\ApplicationEnv;
use Psr\Container\ContainerInterface;
use Jasny\Config;
use Jasny\Config\Loader\JsonLoader;
use Jasny\Config\Loader\YamlLoader;
use Jasny\Config\Loader\DelegateLoader;

return [
    YamlLoader::class => static function() {
        return new YamlLoader(['callbacks' => [
            '!env' => static function ($input) {
                [$env, $value] = explode(' ', $input) + [1 => null];

                return getenv($env) !== false ? getenv($env) : $value;
            }
        ]]);
    },
    DelegateLoader::class => static function(ContainerInterface $container) {
        $loaders = [
            'json' => new JsonLoader(),
            'yaml' => $container->get(YamlLoader::class),
        ];
        $loaders['yml'] = $loaders['yaml'];

        return new DelegateLoader($loaders);
    },
    'config.loader' => static function(ContainerInterface $container) {
        $loader = $container->get(DelegateLoader::class);

        return new AppConfigLoader($loader);
    },
    'config' => static function(ContainerInterface $container) {
        $loader = $container->get('config.loader');
        $env = $container->get(ApplicationEnv::class);

        return (new Config([], $loader))->load($env);
    }
];
