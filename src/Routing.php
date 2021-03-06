<?php
namespace FMUP;

use FMUP\Routing\Route;

/**
 * Class Routing - Routing system where we'll be able to handle multiple route to be handled in a controller
 * @package FMUP
 */
class Routing
{
    const WAY_APPEND = 'WAY_APPEND';
    const WAY_PREPEND = 'WAY_PREPEND';
    /**
     * List of routes to check on routing
     * @var array
     */
    private $routes = array();

    /**
     * @var Request
     */
    private $originalRequest;

    /**
     * Dispatch routes and return the first available route
     * @param Request $request
     * @return Route|null
     */
    public function dispatch(Request $request)
    {
        $this->setOriginalRequest($request);
        $redispatch = false;
        $routeSelected = null;
        $this->defaultRoutes();
        do {
            foreach ($this->getRoutes() as $route) {
                if ($route->setRequest($request)->canHandle()) {
                    //this will handle the request - not fluent interface because we don't know how developer will write
                    $route->handle();
                    $redispatch = $route->hasToBeReDispatched();
                    if (!$redispatch) {
                        $routeSelected = $route;
                    }
                    break;
                }
            }
        } while ($redispatch);
        return $routeSelected;
    }

    /**
     * Define the original request
     * @param Request $request
     * @return $this
     */
    private function setOriginalRequest(Request $request)
    {
        $this->originalRequest = clone $request;
        return $this;
    }

    /**
     * Retrieve original request (nothing has been modified)
     * @return Request|null
     */
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }

    /**
     * Retrieve defined routes
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * Clear all routes defined
     * @return $this
     */
    public function clearRoutes()
    {
        $this->routes = array();
        return $this;
    }

    /**
     * Add a route in stack
     * @param Route $route
     * @param string $way
     * @return $this
     */
    public function addRoute(Route $route, $way = self::WAY_APPEND)
    {
        if ($way == self::WAY_PREPEND) {
            array_unshift($this->routes, $route);
        } else {
            array_push($this->routes, $route);
        }
        return $this;
    }

    /**
     * Can be used to define routes initialized by default
     * @return $this
     */
    public function defaultRoutes()
    {
        return $this;
    }
}
