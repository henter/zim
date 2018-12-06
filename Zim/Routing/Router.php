<?php
/**
 * File Router.php
 * @henter
 * Time: 2018-11-24 20:17
 *
 */

namespace Zim\Routing;

use Zim\App;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Request;
use Zim\Http\Response;

class Router
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * load from config
     *
     * @return RouteCollection
     */
    public static function loadRoutes()
    {
        $routes = new RouteCollection();

        $configRoutes = App::config('routes');
        foreach ($configRoutes as list($pattern, $to)) {
            list($controller, $action) = explode('@', $to);
            //TODO, route name
            $name = $pattern;

            $routes->add($name, new Route($pattern, ['_controller' => 'App\\Controller\\'.$controller.'Controller', '_action' => $action.'Action']));
        }

        return $routes;
    }

    public function __construct()
    {
        $this->routes = self::loadRoutes();
        $this->matcher = new Matcher($this->routes);
    }

    /**
     * Dispatch the request to the application.
     *
     * @param  Request  $request
     * @return Route
     */
    public function dispatch(Request $request)
    {
        return $this->matcher->matchRequest($request);
    }
}