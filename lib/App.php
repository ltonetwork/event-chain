<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rollbar\Rollbar;

/**
 * Service locator for the application.
 * This is actually a wrapper around a container, so if you want to switch to / mix with dependency injection you can.
 * 
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
     * Set or replace the app container
     * 
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
        
        self::configureLocale();
        self::configureDatabase();
    }

    /**
     * Set application locale
     */
    protected static function configureLocale()
    {
        if (!isset(self::config()->locale)) {
            return;
        }
        
        $locale = self::config()->locale;
        
        $locale_charset = setlocale(LC_ALL, "$locale.UTF-8", $locale);
        Locale::setDefault($locale_charset);
        putenv("LC_ALL=$locale_charset");
    }
    
    /**
     * Configure the database connections.
     * @internal Jasny\DB v2 uses it's own service locator and doesn't support dependency injection yet.
     */
    protected static function configureDatabase()
    {
        if (!isset(self::config()->db)) {
            return;
        }
        
        Jasny\DB::$config = self::config()->db;
    }
    
    /**
     * Remove the container and reset other globals
     */
    public static function reset()
    {
        self::$container = null;
        Jasny\DB::resetGlobalState();
    }
    
    
    /**
     * Get and invoke an item from the app container.
     * Will return the item if it is not callable.
     * 
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, array $arguments)
    {
        $item = self::getContainer()->get($name);
        
        return is_callable($item) ? $item(...$arguments) : $item;
    }
    
    
    /**
     * Get the application name
     * 
     * @return string
     */
    public static function name()
    {
        return isset(self::config()->app->name) ? self::config()->app->name : null;
    }

    /**
     * Get the application name
     * 
     * @return string
     */
    public static function version()
    {
        return isset(self::config()->app->version) ? self::config()->app->version : null;
    }

    /**
     * Get the application description
     * 
     * @return string
     */
    public static function description()
    {
        return isset(self::config()->app->description) ? self::config()->app->description : null;
    }
    
    /**
     * Get the application environment.
     * 
     * @param string  $check       Only return if env matches
     * @return string|false
     */
    public static function env($check = null)
    {
        $env = getenv('APPLICATION_ENV') ?: 'dev';
        
        return !isset($check) || $check === $env || strpos($env, $check . '.') === 0 ? $env : false;
    }
    
    
    /**
     * Initialize the application
     */
    public static function init()
    {
        self::setContainer(new AppContainer());
        
        self::initRollbar();
        self::initDisplayErrors();

        self::sessionStart();
    }
    
    /**
     * Get rollbar interface.
     * @internal Global with no way to reset, do not use in tests.
     */
    protected static function initRollbar()
    {
        if (Rollbar::logger() === null && !empty(self::config()->rollbar)) {
            $config = [
                'code_version' => 'v' . self::version(),
                'environment' => self::env(null, false),
                'host' => preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'])
            ];
            $config += (array)self::config()->rollbar;

            Rollbar::init($config);
        }
    }

    /**
     * Set the display errors ini setting.
     * @internal This is changing the global runtime.
     */
    protected static function initDisplayErrors()
    {
        $config = self::config();
        
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

        if (!ini_get('display_errors')) {
            $errorHandler = static::errorHandler();

            $errorHandler->setLogger(self::getContainer()->get('logger'));
            $errorHandler->converErrorsToExceptions();

            if (!static::env('tests')) {
                $errorHandler->logUncaught(E_ALL);
            }
        }
    }
    
    /**
     * Start the session
     */
    public static function sessionStart()
    {
        session_name('plinkr_session');
        session_start();
    }
    
    
    /**
     * Run the application
     */
    public static function run()
    {
        self::init();
        
        $request = self::getContainer()->get(ServerRequestInterface::class);
        $response = self::getContainer()->get(ResponseInterface::class);
        
        self::route($request, $response)->emit();
    }
    
    
    /**
     * Send a message to the browsers console.
     * Works with FireFox (using FirePHP) and Chrome (using Chrome Console)
     * 
     * @param string|mixed $message
     */
    public static function debug($message)
    {
        if (!empty(self::config()->debug)) {
            return;
        }
        
        if (!is_scalar($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (static::env('tests')) {
            Codeception\Util\Debug::debug($message);
            return;
        }
        
        self::logger()->debug($message);
    }
}
