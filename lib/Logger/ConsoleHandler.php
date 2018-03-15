<?php

namespace Logger;

use Jasny\MVC\Request;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\AbstractHandler;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;

/**
 * Brower console handler with failover.
 * Use either the BrowserConsole or FirePHP/ChromePHP handler
 */
class ConsoleHandler extends AbstractHandler
{
    /**
     * Prefer BrowserConsole (js) or FirePHP/ChromePHP (extension)
     * @var string
     */
    protected $prefer = 'js';
    
    /**
     * All handlers
     * @var HandlerInterface[]
     */
    protected $handlers = [];
    
    
    /**
     * Class constructor
     * 
     * @param string $prefer  'js' or 'extension'
     * @param type $level
     * @param type $bubble
     */
    public function __construct($prefer = 'js', $level = Logger::DEBUG, $bubble = true)
    {
        $this->prefer = $prefer;
        
        $this->handlers['js'] = new BrowserConsoleHandler($level, $bubble);
        $this->handlers['firefox'] = new FirePHPHandler($level, $bubble);
        $this->handlers['chrome'] = new ChromePHPHandler($level, $bubble);
                
        parent::__construct($level, $bubble);
    }

    /**
     * Handles a record.
     *
     * @param  array   $record The record to handle
     * @return boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        if (isset($record['context']['exception'])) {
            $e = $record['context']['exception'];
            $record['context']['exception'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTrace()
            ];
        }
        
        $handler = $this->pickHandler();
        if ($handler) return $handler->handle($record);
        
        return false;
    }

    /**
     * Pick one of the handlers based on HTTP Request and Response headers
     * 
     * @return BrowserConsoleHandler|FirePHPHandler|ChromePHPHandler
     */
    public function pickHandler()
    {
        $type = $this->prefer;
        $isHTML = self::isHTMLRequest();
        
        if ($type === 'js' && !$isHTML) $type = 'extension';

        if ($type === 'extension') {
            $type = null;
            if (static::checkUserAgent('firefox')) $type = 'firefox';
             elseif (static::checkUserAgent('chrome')) $type = 'chrome';
             elseif ($isHTML) $type = 'js';
        }
        
        return isset($type) ? $this->handlers[$type] : null;
    }
        
    /**
     * Check if current request is an HTML request and also not AJAX.
     * 
     * @return boolean
     */
    protected static function isHTMLRequest()
    {
        return !Request::isAjax() && Request::getOutputFormat() === 'html';
    }
    
    /**
     * Check if string is part of the user agent
     * 
     * @param string $string
     * @return boolean
     */
    protected static function checkUserAgent($string)
    {
        return isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], $string) !== false;
    }        
}
