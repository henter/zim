<?php
/**
 * File Router.php
 * @henter
 * Time: 2018-11-24 20:17
 *
 */

namespace Zim\Routing;


use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var UrlMatcher
     */
    protected $matcher;

    public static function demoConfig()
    {
        return [
            //['/', 'Index@index'],
            ['/test', 'Index@test'],
            ['/foo', 'Foo@index'],
            ['/foo/test', 'Foo@foo'],
        ];
    }

    public static function demoRoutes()
    {
        $routes = new RouteCollection();

        $configRoutes = self::demoConfig();
        foreach ($configRoutes as list($pattern, $to)) {
            list($controller, $action) = explode('@', $to);
            $routes->add($pattern, new Route($pattern, ['_controller' => 'App\\Controller\\'.$controller.'Controller', '_action' => $action.'Action']));
        }

        return $routes;
    }

    public function __construct()
    {
        $this->routes = self::demoRoutes();
        $this->matcher = new UrlMatcher($this->routes, new RequestContext('/'));
    }

    /**
     * @param $uri
     * @return array
     */
    public function match($uri)
    {
        return $this->matcher->match($uri);
    }

}