<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;

use Zim\Event\DispatchEvent;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Event\TerminateEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Http\Request;
use Zim\Http\Response;
use Zim\Http\Controller;
use Zim\Routing\Router;
use Zim\Support\Str;

trait Handler
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request) :Response
    {
        $this->instance('request', $request);
        $this->boot();

        $requestEvent = new RequestEvent($request);
        $this->fire($requestEvent);
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
        $this->fire($respEvent);
        return $respEvent->getResponse()->prepare($request);
    }

    /**
     * 根据 uri 猜测 controller 类名
     * @param string $uri
     * @return string|bool
     */
    private function guessController($uri)
    {
        if (class_exists('App\\Controller\\'.ucfirst($uri).'Controller')) {
            return ucfirst($uri);
        }

        $suffix = 'Controller.php';
        $files = glob(APP_PATH. '/Controller/*'.$suffix);
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
    public function dispatchToDefault(Request $request) :Response
    {
        //FooController index
        [$c, $a] = $this->getDefaultRoute($request);

        /**
         * @var Controller $controller
         */
        $controller = $this->make('App\\Controller\\'.$c.'Controller');

        //try controller action ?
        if ($method = $controller->getAction($a)) {
            $callable = [$controller, $method];
        } else {
            //try controller action class
            if (!class_exists($actionClass = $controller->getActionClass($a))) {
                throw new NotFoundException('action not found');
            }
            $callable = [$this->make($actionClass), 'execute'];
        }

        return $this->doDispatch($request, $callable);
    }

    /**
     * 基于路由规则匹配
     *
     * @param Request $request
     * @return Response
     */
    public function dispatchToRouter(Request $request) :Response
    {
        $route = $this->getRouter()->matchRequest($request);
        if (!$callable = $route->getDefault('_callable')) {
            $callable = [$this->make($route->getDefault('_controller')), $route->getDefault('_action')];
        }

        return $this->doDispatch($request, $callable, $route->getParameters());
    }

    /**
     * @return Router
     */
    private function getRouter()
    {
        return $this->make('router');
    }

    /**
     * @param Request $request
     * @param callable $callable
     * @param array $params
     * @return Response
     */
    private function doDispatch(Request $request, callable $callable, $params = []) :Response
    {
        if (is_array($callable)) {
            $request->attributes->set('callable', [get_class($callable[0]), $callable[1]]);
        } else {
            $request->attributes->set('callable', ['Closure', 'Closure']);
        }

        $e = new DispatchEvent($request);
        $this->fire($e);
        if ($resp = $e->getResponse()) {
            return $resp->prepare($request);
        }

        return $this->toResponse($this->call($callable, $params));
    }

    /**
     * @param mixed $resp
     * @return Response
     */
    private function toResponse($resp) :Response
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
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        $this->fire(new TerminateEvent($request, $response));
    }

}