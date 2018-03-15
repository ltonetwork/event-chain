<?php

namespace Logger;

use App;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\RollbarHandler;

/**
 * Monolog Factory that uses the configuration
 * 
 * @todo implement all handlers.
 */
class Factory
{
    /**
     * Create a new Logger
     * 
     * @param mixed $settings
     * @return Logger
     */
    public static function createLogger($settings=null)
    {
        if (!isset($settings)) {
            $settings = isset(App::config()->log) ? App::config()->log : true;
        }
        
        $name = App::url();
        $handlers = [];
        $processors = [];

        if (is_bool($settings)) {
            $handlers[] = $settings ? 'ErrorHandler' : 'Output';
        } elseif (is_string($settings)) {
            $handlers[] = $settings;
        } elseif (is_object($settings) && !isset($settings->handlers)) {
            $handlers[] = $settings;
        } elseif (is_array($settings)) {
            $handlers = $settings;
        } elseif (is_object($settings)) {
            $name = isset($settings->name) ? $settings->name : null;
            $handlers = isset($settings->handlers) ? $settings->handlers : null;
            $processors = isset($settings->processors) ? $settings->processors : null;
        }
            
        foreach ($handlers as $i=>&$handler) {
            if ($handler instanceof HandlerInterface) continue;
            
            $handler = static::createHandler($handler);
            if (!isset($handler)) unset($handlers[$i]);
        }
        
        return new Logger($name, $handlers, $processors);
    }
    
    /**
     * Create a new handler
     * 
     * @param object|array|string $settings
     * @return HandlerInterface
     */
    public static function createHandler($settings)
    {
        if (is_scalar($settings)) $settings = (object)array('type' => $settings);
        if (is_array($settings)) $settings = (object)$settings;
        
        if (empty($settings->type)) return;
        $settings->type = ucfirst($settings->type);
        
        if (!isset($settings->level)) $settings->level = null;
        if (is_string($settings->level)) $settings->level = constant("Monolog\\Logger::{$settings->level}");

        if (!isset($settings->bubble)) $settings->bubble = true;
        
        $fn = "create{$settings->type}Handler";
        if (method_exists(__CLASS__, $fn)) {
            return call_user_func(array(__CLASS__, $fn), $settings);
        }

        $class = "Monolog\\Handler\\{$settings->type}Handler";
        if (class_exists($class)) {
            return isset($settings->level) ? new $class($settings->level) : new $class();
        }

        trigger_error("Unknown Monolog handler '{$settings->type}'.", E_USER_WARNING);
    }

    
    /**
     * Create a streamhandler to output
     * 
     * @param object $settings
     * @return StreamHandler
     */
    protected static function createOutputHandler($settings)
    {
        return new StreamHandler('php://output', $settings->level ?: Logger::DEBUG, $settings->bubble);
    }
    
    /**
     * Get failover console handler.
     * 
     * @param object $settings
     * @return ConsoleHandler
     */
    protected static function createConsoleHandler($settings)
    {
        $prefer = isset($settings->prefer) ? $settings->prefer : 'js';
        return new ConsoleHandler($prefer, $settings->level ?: Logger::DEBUG, $settings->bubble);
    }

    /**
     * Configure Rollbar handler
     * @link https://rollbar.com/
     * 
     * @param object $settings
     * @return RollbarHandler
     */
    protected static function createRollbarHandler($settings)
    {
        $config = array(
            'environment' => App::env(),
            'root' => getcwd(),
            'max_errno' => E_USER_NOTICE,  // ignore E_STRICT and above
            'person_fn' => function() {
                $user = Auth::user();
                if (!isset($user)) return null;
                    
                return array(
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail()
                );
            }
        );
        
        if (isset(App::config()->rollbar)) $config = array_merge($config, App::config()->rollbar);
        if (isset($settings->config)) $config = array_merge($config, (array)$settings->config);

        $notifier = new RollbarNotifier($config);
        return new RollbarHandler($notifier, $settings->level ?: Logger::ERROR, $settings->bubble);
    }
}
