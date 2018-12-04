<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;

use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Http\Request;
use Zim\Http\Response;
use Zim\Http\Controller;

trait RouteRequest
{

    /**
     * @param null $request
     * @throws \Throwable
     */
    public function run($request = null)
    {
        $request = $request ?: Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    public function terminate(Request $request, Response $response)
    {
        $this->dispatch(Event::TERMINATE);
    }

    /**
     * @param  array $routeInfo
     * @return mixed|string|array|bool|Response
     */
    protected function callControllerAction($routeInfo)
    {
        $controller = $this->make($routeInfo['_controller']);
        if (!method_exists($controller, $routeInfo['_action'])) {
            throw new NotFoundException('method not found');
        }
        return $controller->{$routeInfo['_action']}();
    }

    /**
     * 根据 uri 猜测 controller 类名
     * @param string $uri
     * @return string|bool
     */
    private function guessAppController($uri)
    {
        if (class_exists('App\\Controller\\'.ucfirst($uri).'Controller')) {
            return ucfirst($uri).'Controller';
        }

        $files = glob(APP_PATH. '/Controller/*Controller.php');
        foreach ($files as $file) {
            if (strtolower(basename($file)) == $uri) {
                return rtrim(basename($file), '.php');
            }
        }
        return false;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function tryCallControllerAction(Request $request)
    {
        //default IndexController IndexAction, same as yaf
        $segments = explode('/', $request->getPathInfo());
        if ($request->getPathInfo() == '/') {
            $c = $a = 'index';
        } else if (count($segments) <= 2) {
            [, $c] = $segments;
            $a = 'index';
        } else {
            [, $c, $a] = $segments;
        }

        //try controller
        if (!$c = $this->guessAppController($c)) {
            throw new NotFoundException('path not found');
        }

        /**
         * @var Controller $controller
         */
        $controller = $this->make('App\\Controller\\'.$c);

        //try controller method ?
        if ($method = $controller->getMethod($a)) {
            return $controller->{$method}();
        }

        //try controller action class
        $actionClass = $controller->getAction($a);
        if (!class_exists($actionClass)) {
            throw new NotFoundException('action not found');
        }

        return $this->make($actionClass)->execute();
    }

    /**
     * @param Request|null $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        $this->boot();
        $this->instance('request', $request);

        try {
            $requestEvent = new RequestEvent($request);
            $this->dispatch($requestEvent);

            if ($resp = $requestEvent->getResponse()) {
                return $resp->prepare($request);
            }

            $routeInfo = $this->router->match($request->getPathInfo(), $request->getMethod());
            $actionReturn = $this->callControllerAction($routeInfo);
        } catch (NotFoundException $e) {
            $actionReturn = $this->tryCallControllerAction($request);
        } catch (\Throwable $e) {
            //TODO, after replaced router
            //return $this->errorResponse('exception '.$e->getMessage())->prepare($request);
            throw $e;
        }

        if ($actionReturn instanceof Response) {
            $response = $actionReturn;
        } else if (is_array($actionReturn) || is_scalar($actionReturn)) {
            $response = new Response($actionReturn);
        } else {
            throw new ResponseException('invalid response');
        }

        $respEvent = new ResponseEvent($request, $response);
        $this->dispatch($respEvent);

        return $respEvent->getResponse()->prepare($request);
    }

    /**
     * @param $msg
     * @return Response
     */
    public function errorResponse($msg)
    {
        return new Response($msg);
    }
}