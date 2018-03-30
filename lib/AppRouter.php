<?php

use Psr\Container\ContainerInterface;
use Jasny\Router;
use Jasny\ErrorHandlerInterface;

/**
 * Application router.
 * Load all the middleware this application wants to use.
 *
 * @codeCoverageIgnore
 */
class AppRouter extends Router
{
    /**
     * Add the middleware this application uses.
     * 
     * @param ContainerInterface $container
     */
    public function withMiddleware(ContainerInterface $container)
    {
        return $this
            ->withDetermineRouteMiddleware();
    }
    
    /**
     * Add middleware to show a nice error page instead of a blank screen
     * 
     * @return $this
     */
    protected function withErrorHandlerMiddleware(ContainerInterface $container)
    {
        $errorHandler = $container->get(ErrorHandlerInterface::class);
        
        return $this->add($errorHandler->asMiddleware());
    }
    
    /**
     * Add middleware to show a nice error page instead of a blank screen
     * 
     * @return $this
     */
    protected function withErrorPageMiddleware(ContainerInterface $container)
    {
        return App::env('tests') ? $this : $this->add(new Router\Middleware\ErrorPage($this));
    }

    /**
     * Determine the routes at forehand
     * 
     * @return $this
     */
    protected function withDetermineRouteMiddleware()
    {
        $routes = $this->getRoutes(); // Not expecting the routes to change later.
        
        return $this->add(new Router\Middleware\DetermineRoute($routes));
    }
}
