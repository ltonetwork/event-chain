<?php

/**
 * Description of ErrorController
 *
 * @author arnold
 */
class ErrorController extends BaseController
{
    /**
     * 403 forbidden
     * 
     * @param string $message
     * @param string $code     HTTP status code
     */
    public function forbiddenAction($message = null, $code = 403)
    {
        $this->respondWith($code);
        $this->view('error/403', compact('message', 'code'));
    }
    
    /**
     * 404 not found
     * 
     * @param string $message
     * @param string $code     HTTP status code
     */
    public function notFoundAction($message = null, $code = 404)
    {
        $this->respondWith($code);
        $this->view('error/404', compact('message', 'code'));
    }
    
    /**
     * 500 Internal Server Error
     * 
     * @param string $message
     * @param string $code     HTTP status code
     */
    public function errorAction($message = null, $code = 500)
    {
        $this->respondWith($code);
        $this->view('error/500', compact('message', 'code'));
    }
}
