<?php
/**
 * File Kernel.php
 * @henter
 * Time: 2018-11-26 16:07
 */

namespace Zim\Http;

use Zim\Event\DispatchEvent;
use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Event\TerminateEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Routing\Router;
use Zim\Support\Str;
use Zim\Zim;

class Kernel
{
    /**
     * @var Zim
     */
    protected $zim;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Create a new HTTP kernel instance.
     *
     * @param  Zim    $zim
     * @param  Router $router
     * @return void
     */
    public function __construct(Zim $zim, Router $router)
    {
        $this->zim = $zim;
        $this->router = $router;
        $this->bootstrapRoutes();
    }

    protected function bootstrapRoutes()
    {
        $configs = Zim::config('routes');
        //just in-case if routes.php not returned any configs as the value would be 1
        if (!is_array($configs)) {
            return false;
        }

        foreach ($configs as $pattern => $to) {
            $this->router->addRoute([], $pattern, $to);
        }
        return true;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {
        $this->zim->instance('request', $request);
        $this->zim->boot();

        $requestEvent = new RequestEvent($request);
        Event::fire($requestEvent);
        if ($resp = $requestEvent->getResponse()) {
            return $resp->prepare($request);
        }

        try {
            $response = $this->dispatchToRouter($request);
        } catch (NotFoundException $e) {
            $response = $this->dispatchToDefault($request);
        } catch (\Throwable $e) {
            throw $e;
        }

        $respEvent = new ResponseEvent($request, $response);
        Event::fire($respEvent);
        return $respEvent->getResponse()->prepare($request);
    }

    /**
     * 根据 uri 猜测 controller 类名
     * @param string $uri
     * @return string|bool
     */
    private function guessController($uri)
    {
        if (class_exists('App\\Controller\\' . ucfirst($uri) . 'Controller')) {
            return ucfirst($uri);
        }

        $suffix = 'Controller.php';
        $files = glob(APP_PATH . '/Controller/*' . $suffix);
        foreach ($files as $file) {
            $name = Str::replaceLast($suffix, '', basename($file));
            if ($uri === strtolower($name)) {
                return $name;
            }
        }
        return false;
    }

    /**
     * default IndexController indexAction, same as yaf
     *
     * rules:
     * /            => Index@index
     * /foo         => Foo@index or Index@foo
     * /foo/bar     => Foo@bar
     *
     * @param Request $request
     * @return array [Index, index]
     */
    private function getDefaultRoute(Request $request)
    {
        $segments = array_filter(explode('/', trim($request->getPathInfo(), '/')));
        if (!$segments) {
            return ['Index', 'index'];
        }

        [$c, $a] = isset($segments[1]) ? $segments : [$segments[0], 'index'];

        //如果 FooController 不存在，则尝试调度到 IndexController@fooAction
        if (!$c = $this->guessController($c)) {
            $c = 'Index';
            $a = $segments[0];
        }
        return [$c, $a];
    }

    /**
     * 默认路由规则
     * 即：
     *      如果存在 FooController
     *      /foo     => App\Controller\FooController::indexAction
     *      /foo/bar => App\Controller\FooController::barAction
     *      /foo/bar => App\Controller\FooController::$actions[bar]::execute
     *
     *      否则
     *      /foo     => App\Controller\IndexController::fooAction
     *      /foo/bar => App\Controller\IndexController::fooAction
     *
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function dispatchToDefault(Request $request): Response
    {
        //FooController index
        [$c, $a] = $this->getDefaultRoute($request);

        /**
         * @var Controller $controller
         */
        $controller = $this->zim->make('App\\Controller\\' . $c . 'Controller');

        //try controller action ?
        if ($method = $controller->getAction($a)) {
            $callable = [$controller, $method];
        } else {
            //try controller action class
            if (!class_exists($actionClass = $controller->getActionClass($a))) {
                throw new NotFoundException('action not found');
            }
            $callable = [$this->zim->make($actionClass), 'execute'];
        }

        return $this->doDispatch($request, $callable);
    }

    /**
     * 基于路由规则匹配
     *
     * @param Request $request
     * @return Response
     */
    public function dispatchToRouter(Request $request): Response
    {
        $route = $this->router->matchRequest($request);
        if (!$callable = $route->getDefault('_callable')) {
            $callable = [$this->zim->make($route->getDefault('_controller')), $route->getDefault('_action')];
            if (!is_callable($callable)) {
                throw new NotFoundException('action not found '.$callable[1]);
            }
        }

        return $this->doDispatch($request, $callable, $route->getParameters());
    }

    /**
     * @param Request  $request
     * @param callable $callable
     * @param array    $params
     * @return Response
     */
    private function doDispatch(Request $request, callable $callable, $params = []): Response
    {
        if (is_array($callable)) {
            $request->attributes->set('callable', [get_class($callable[0]), $callable[1]]);
        } else {
            $request->attributes->set('callable', ['Closure', 'Closure']);
        }

        $e = new DispatchEvent($request);
        Event::fire($e);
        if ($resp = $e->getResponse()) {
            return $resp->prepare($request);
        }

        return $this->toResponse($this->zim->call($callable, $params));
    }

    /**
     * @param mixed $resp
     * @return Response
     */
    private function toResponse($resp): Response
    {
        if ($resp instanceof Response) {
            $response = $resp;
        } else if (is_array($resp) || is_scalar($resp) || is_null($resp)) {
            $response = new Response($resp);
        } else {
            throw new ResponseException(500, 'invalid response');
        }
        return $response;
    }

    /**
     * will not return to fastcgi
     *
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        Event::fire(new TerminateEvent($request, $response));
    }

}