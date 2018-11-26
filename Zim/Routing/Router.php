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
use Zim\App;
use Zim\Http\Exception\NotFoundException;

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

    /**
     * load from config
     *
     * @return RouteCollection
     */
    public static function loadRoutes()
    {
        $routes = new RouteCollection();

        $configRoutes = App::getInstance()->make('config')->get('routes');
        foreach ($configRoutes as list($pattern, $to)) {
            list($controller, $action) = explode('@', $to);
            $routes->add($pattern, new Route($pattern, ['_controller' => 'App\\Controller\\'.$controller.'Controller', '_action' => $action.'Action']));
        }

        return $routes;
    }

    public function __construct()
    {
        $this->routes = self::loadRoutes();
        $this->matcher = new UrlMatcher($this->routes, new RequestContext('/'));
    }

    /**
     * @param $uri
     * @return array
     */
    public function match($uri)
    {
        try {
            return $this->matcher->match($uri);
        } catch (\Exception $e) {
            throw new NotFoundException($uri.' not found');
        }
    }

}