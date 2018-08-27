<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\Config;
use Jasny\Container\Container;
use Jasny\Container\Loader\EntryLoader;
use Jasny\DB;

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
    public static function getContainer()
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
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;

        self::initDB();
    }

    /**
     * Initialize the DB
     */
    protected static function initDB()
    {
        DB::resetGlobalState();
        DB::configure(self::config()->db);
    }


    /**
     * Get and invoke an item from the app container.
     * Will return the item if it is not callable.
     * @deprecated
     * 
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, array $arguments)
    {
        trigger_error("Using App as service locator is deprecated", E_USER_WARNING);

        $item = self::getContainer()->get($name);
        
        return is_callable($item) ? $item(...$arguments) : $item;
    }
    

    /**
     * Get the application environment.
     * 
     * @param string  $check       Only return if env matches
     * @return string|false
     */
    public static function env($check = null)
    {
        $env = self::getContainer()->get('app.env');
        
        return !isset($check) || $check === $env || strpos($env, $check . '.') === 0 ? $env : false;
    }

    /**
     * Get the application configuration
     *
     * @return Config
     */
    public static function config()
    {
        return self::getContainer()->get('config');
    }

    
    /**
     * Initialize the application
     */
    public static function init()
    {
        $container = new Container(self::getContainerEntries());
        self::setContainer($container);

        self::initGlobal();
    }

    /**
     * @return EntryLoader
     */
    public static function getContainerEntries()
    {
        $files = new ArrayIterator(glob('declarations/{services,models}/*.php', GLOB_BRACE));

        return new EntryLoader($files);
    }

    /**
     * Init global environment
     */
    protected static function initGlobal()
    {
        $scripts = glob('declarations/global/*.php');

        foreach ($scripts as $script) {
            require_once $script;
        }
    }
    
    /**
     * Run the application
     */
    public static function run()
    {
        self::init();
        self::handleRequest();
    }

    /**
     * Use the router to handle the current HTTP request.
     */
    protected static function handleRequest()
    {
        $container = self::getContainer();

        /* @var $router \Jasny\Router */
        $router = $container->get('router');

        $request = $container->get(ServerRequestInterface::class);
        $response = $container->get(ResponseInterface::class);

        $router->handle($request, $response)->emit();
    }
    
    
    /**
     * Send a message to the configured logger.
     * 
     * @param string|mixed $message
     */
    public static function debug($message)
    {
        if (!(self::config()->debug ?? false)) {
            return;
        }

        if (!is_scalar($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (self::env('tests')) {
            Codeception\Util\Debug::debug($message);
            return;
        }

        self::getContainer()->get('logger')->debug($message);
    }
}
