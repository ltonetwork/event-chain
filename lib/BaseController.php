<?php

use Jasny\View\Twig;

/**
 * Base class for controllers.
 */
abstract class BaseController extends Jasny\Controller
{
    use Jasny\Controller\Session,
        Jasny\Controller\RouteAction,
        Jasny\Controller\View\Twig {
            view as _view;
        }

    /**
     * Auth instance
     * @var Auth
     **/
    public $auth;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->auth = App::auth();
    }

    /**
     * Get the template engine abstraction
     * 
     * @return Jasny\View\Twig
     */
    protected function getViewer()
    {
        if (!isset($this->viewer)) {
            $this->viewer = App::getContainer()->get('view');
        }

        return $this->viewer;
    }
    
    /**
     * Get the Twig view path
     * 
     * @return string
     */
    protected function getViewPath()
    {
        return getcwd() . '/views';
    }

    /**
     * Perform 'Bad Request' response
     * 
     * @param string $message
     * @param int $code
     */
    public function badRequest($message, $code = 400)
    {
        if ($this->isXhr()) {
            parent::badRequest($message, $code);
        } else {
            $this->flash('danger', $message);
            $this->back();
        }
    }
    
    /**
     * Returns the HTTP referer if it is on the current host.
     * @internal Fix for https://github.com/jasny/controller/issues/14
     *
     * @return string
     */
    public function getLocalReferer()
    {
        $request = $this->getRequest();
        $referer = $request->getHeaderLine('Referer');
        $host = $request->getHeaderLine('Host');

        return !empty($referer) && parse_url($referer, PHP_URL_HOST) === $host ? $referer : '';
    }
    
    /**
     * Check if this is an AJAX call
     * 
     * @return boolean
     */
    public function isXhr()
    {
        return $this->getRequest()->getAttribute('is_xhr', false);
    }

    /**
     * Get url for user registration confimation
     *
     * @param string $path
     * @param string $hash
     * @return string
     */
    public function getSignupUrl($path, $hash)  
    {
        return (string)$this->getRequest()->getUri()
            ->withPath($path)
            ->withQuery('c=' . $hash)
            ->withPort('');
    }
}
