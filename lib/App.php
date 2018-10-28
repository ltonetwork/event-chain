<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\Config;
use Jasny\Container\Container;
use Jasny\Container\Loader\EntryLoader;
use Jasny\DB;
use Jasny\RouterInterface;
use Jasny\HttpMessage\Emitter;

/**
 * Application
 * @codeCoverageIgnore
 */
class App
{
    /**
     * @var ContainerInterface
     */
    public static $container;

    
    /**
     * This is a static class, it should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Get the app container
     *
     * @return ContainerInterface
     * @throws LogicException if the container is not set yet
     */
    public static function getContainer(): ContainerInterface
    {
        if (!isset(self::$container)) {
            throw new LogicException("This container is not set");
        }
        
        return self::$container;
    }

    /**
     * Set the container
     *
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
        self::initDB();
    }

    /**
     * Initialize the DB
     */
    protected static function initDB(): void
    {
        DB::resetGlobalState();
        DB::configure(self::config()->db);
    }


    /**
     * Get the application environment.
     *
     * @param string  $check       Only return if env matches
     * @return string|bool
     */
    public static function env($check = null)
    {
        /** @var Jasny\ApplicationEnv $env */
        $env = self::getContainer()->get('app.env');

        return isset($check) ? $env->is($check) : (string)$env;
    }

    /**
     * Get the application configuration
     *
     * @return Config
     */
    public static function config(): Config
    {
        return self::getContainer()->get('config');
    }

    
    /**
     * Initialize the application
     */
    public static function init(): void
    {
        $container = new Container(self::getContainerEntries());
        self::setContainer($container);

        self::initGlobal();
    }

    /**
     * @return EntryLoader&iterable<Closure>
     */
    public static function getContainerEntries(): EntryLoader
    {
        $files = new ArrayIterator(glob('declarations/{generic,models}/*.php', GLOB_BRACE));

        /** @var EntryLoader&iterable<Closure> $entryLoader */
        $entryLoader = new EntryLoader($files);

        return $entryLoader;
    }

    /**
     * Init global environment
     */
    protected static function initGlobal(): void
    {
        $scripts = glob('declarations/global/*.php');

        foreach ($scripts as $script) {
            /** @noinspection PhpIncludeInspection */
            require_once $script;
        }
    }
    
    /**
     * Run the application
     */
    public static function run(): void
    {
        self::init();
        self::handleRequest();
    }

    /**
     * Use the router to handle the current HTTP request.
     */
    protected static function handleRequest(): void
    {
        $container = self::getContainer();

        /* @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);

        $request = $container->get(ServerRequestInterface::class);
        $baseResponse = $container->get(ResponseInterface::class);

        $response = $router->handle($request, $baseResponse);

        /** @var Emitter $emitter */
        $emitter = $container->get(Emitter::class);
        $emitter->emit($response);
    }
    
    
    /**
     * Send a message to the configured logger.
     *
     * @param string|mixed $message
     */
    public static function debug($message): void
    {
        if (!(self::config()->debug ?? false)) {
            return;
        }

        if (!is_scalar($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if ((bool)self::env('tests')) {
            Codeception\Util\Debug::debug($message);
            return;
        }

        self::getContainer()->get('logger')->debug($message);
    }
}
