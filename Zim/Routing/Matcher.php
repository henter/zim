<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zim\Routing;

use Zim\Http\Exception\MethodNotAllowedException;
use Zim\Http\Exception\NotFoundException;

/**
 * UrlMatcher matches URL based on a set of routes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Matcher
{
    protected $context;

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
     * Collects HTTP methods that would be allowed for the request.
     */
    protected $allow = [];

    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo, $method = 'GET')
    {
        $this->method = $method;
        $this->allow = [];

        if ($ret = $this->matchCollection(rawurldecode($pathinfo), $this->routes)) {
            return $ret;
        }

        if ('/' === $pathinfo && !$this->allow) {
            throw new NotFoundException();
        }

        throw 0 < \count($this->allow)
            ? new MethodNotAllowedException(array_unique($this->allow))
            : new NotFoundException(sprintf('No routes found for "%s".', $pathinfo));
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * @param string          $pathinfo The path info to be parsed
     * @param RouteCollection $routes   The set of routes
     *
     * @return array An array of parameters
     * @throws NotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    protected function matchCollection($pathinfo, RouteCollection $routes)
    {
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())) {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                continue;
            }

            if ($requiredMethods = $route->getMethods()) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->method) {
                    $method = 'GET';
                }

                if (!\in_array($method, $requiredMethods)) {
                    continue;
                }
            }

            return $this->getAttributes($route, $name, $matches);
        }
    }

    /**
     * Returns an array of values to use as request attributes.
     *
     * As this method requires the Route object, it is not available
     * in matchers that do not have access to the matched Route instance
     * (like the PHP and Apache matcher dumpers).
     *
     * @param Route  $route      The route we are matching against
     * @param string $name       The name of the route
     * @param array  $attributes An array of attributes from the matcher
     *
     * @return array An array of parameters
     */
    protected function getAttributes(Route $route, $name, array $attributes)
    {
        $defaults = $route->getDefaults();
        if (isset($defaults['_canonical_route'])) {
            $name = $defaults['_canonical_route'];
            unset($defaults['_canonical_route']);
        }
        $attributes['_route'] = $name;

        return $this->mergeDefaults($attributes, $defaults);
    }

    /**
     * Get merged default parameters.
     *
     * @param array $params   The parameters
     * @param array $defaults The defaults
     *
     * @return array Merged default parameters
     */
    protected function mergeDefaults($params, $defaults)
    {
        foreach ($params as $key => $value) {
            if (!\is_int($key) && null !== $value) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

}
