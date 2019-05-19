<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\Container\AutowireContainerInterface;
use Jasny\Router\ControllerFactory;
use Jasny\Router\RoutesInterface;
use Jasny\RouterInterface;
use Jasny\Router;
use Jasny\Router\Routes;
use Jasny\Router\Runner\Controller as Runner;
use Symfony\Component\Yaml\Yaml;

return [
    'router.routes' => function () {
        $yaml = file_get_contents('config/routes.yml');
        if ($yaml === false) {
            throw new \Exception("Failed to open 'config/routes.yml");
        }

        return Yaml::parse($yaml);
    },
    'router.middleware' => function () {
        $sources = glob('declarations/middleware/*.php');

        return array_reduce($sources, function (array $middleware, string $source) {
            /** @noinspection PhpIncludeInspection */
            $declaration = include $source;

            return $middleware + $declaration;
        }, []);
    },
    'router.runner' => function (ContainerInterface $container) {
        return (new Runner)->withFactory($container->get(ControllerFactory::class));
    },

    ControllerFactory::class => function (AutowireContainerInterface $container) {
        return new ControllerFactory(function (string $controllerClass) use ($container) {
            return $container->autowire($controllerClass);
        });
    },
    RoutesInterface::class => function (ContainerInterface $container) {
        return new Routes\Glob($container->get('router.routes'));
    },
    RouterInterface::class => function (ContainerInterface $container) {
        $router = new Router($container->get(RoutesInterface::class));
        $router->setRunner($container->get('router.runner'));

        foreach ($container->get('router.middleware') as $fn) {
            $router->add($fn($router, $container));
        }

        return $router;
    },

    // Alias
    'router' => function (ContainerInterface $container) {
        return $container->get(RouterInterface::class);
    }
];
