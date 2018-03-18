<?php

use Psr7Middlewares\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Jasny\Router;
use Jasny\Auth;
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
            ->withMethodOverwriteMiddleware()
            ->withDetermineRouteMiddleware()
            ->withAuthMiddleware($container);
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
     * Set the HTTP method to DELETE using the _method parameter.
     * 
     * @return $this
     */
    protected function withMethodOverwriteMiddleware()
    {
        $methodOverride = Middleware::MethodOverride()->post(['DELETE'])->parameter('_method', false);
        
        return $this->add($methodOverride);
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

    /**
     * Add middleware for authorization and access control
     * 
     * @return $this
     */
    protected function withAuthMiddleware(ContainerInterface $container)
    {
        if (!$container->has(Auth::class)) {
            return;
        }
        
        $auth = $container->get(Auth::class);
        $routes = $this->getRoutes(); // Not expecting the routes to change later.
        
        $getRoleFn = function(ServerRequestInterface $request) use ($routes) {
            $route = $routes->getRoute($request);
            return isset($route->authz) ? $route->authz : null;
        };
        
        return $this->add($auth->asMiddleware($getRoleFn));
    }
}
