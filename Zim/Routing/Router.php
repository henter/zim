<?php
/**
 * File Router.php
 * @henter
 * Time: 2018-11-24 20:17
 *
 */

namespace Zim\Routing;

use Zim\Http\Exception\MethodNotAllowedException;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Request;

class Router
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * current method
     *
     * @var string
     */
    protected $method;

    /**
     * current allowed methods for the request.
     */
    protected $allow = [];

    /**
     * Router constructor.
     * @param RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Dispatch the request to the application.
     *
     * @param  Request  $request
     * @return Route
     */
    public function matchRequest(Request $request) :Route
    {
        return $this->match($request->getPathInfo(), $request->getMethod());
    }

    /**
     * @param $path
     * @param string $method
     * @return Route
     */
    public function match($path, $method = 'GET') :Route
    {
        $this->method = $method;
        $this->allow = [];

        if ($route = $this->matchCollection(rawurldecode($path), $this->routes)) {
            return $route;
        }

        if ('/' === $path && !$this->allow) {
            throw new NotFoundException();
        }

        throw 0 < \count($this->allow)
            ? new MethodNotAllowedException(array_unique($this->allow))
            : new NotFoundException(sprintf('No routes found for "%s".', $path));
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * @param string          $path The path info to be parsed
     * @param RouteCollection $routes   The set of routes
     *
     * @return Route|null
     * @throws NotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    protected function matchCollection($path, RouteCollection $routes)
    {
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();
            $staticPrefix = $compiledRoute->getStaticPrefix();

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' === $staticPrefix || 0 === strpos($path, $staticPrefix)) {
                // no-op
            } elseif ('/' === $staticPrefix[-1] && substr($staticPrefix, 0, -1) === $path) {
                return null;
            } elseif ('/' === $path[-1] && substr($path, 0, -1) === $staticPrefix) {
                return null;
            } else {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $path, $matches)) {
                continue;
            }

            if ($requiredMethods = $route->getMethods()) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->method) {
                    $method = 'GET';
                }

                if (!\in_array($method, $requiredMethods)) {
                    $this->allow = array_merge($this->allow, $requiredMethods);
                    continue;
                }
            }

            $route->setParameters(array_slice($matches, 1));
            return $route;
        }
        return null;
    }
}