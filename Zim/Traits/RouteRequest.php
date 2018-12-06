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
use Zim\Routing\Router;

trait RouteRequest
{
    /**
     * @var Router
     */
    protected $router;

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
        $this->fire(Event::TERMINATE);
    }

    /**
     * @param Request|null $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request)
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
            //return $this->errorResponse('exception '.$e->getMessage())->prepare($request);
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
            return ucfirst($uri).'Controller';
        }

        $suffix = 'Controller.php';
        $files = glob(APP_PATH. '/Controller/*'.$suffix);
        foreach ($files as $file) {
            $name = rtrim(basename($file), $suffix);
            if (strtolower($name) == $uri) {
                return $name.'Controller';
            }
        }
        return false;
    }

    /**
     * default IndexController indexAction, same as yaf
     *
     * @param Request $request
     * @return array
     */
    private function getDefaultRoute(Request $request)
    {
        $segments = explode('/', $request->getPathInfo());
        if ($request->getPathInfo() == '/') {
            $c = $a = 'Index';
        } else if (count($segments) <= 2) {
            [, $c] = $segments;
            $a = 'index';
        } else {
            [, $c, $a] = $segments;
        }

        return [$c, $a];
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function dispatchToDefault(Request $request) :Response
    {
        [$c, $a] = $this->getDefaultRoute($request);

        //try controller
        if (!$c = $this->guessController($c)) {
            throw new NotFoundException('path not found');
        }

        /**
         * @var Controller $controller
         */
        $controller = $this->make('App\\Controller\\'.$c);

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

        return $this->toResponse($this->call($callable));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dispatchToRouter(Request $request) :Response
    {
        $route = $this->router->dispatch($request);
        $callable = [$this->make($route->getDefault('_controller')), $route->getDefault('_action')];

        $return = $this->call($callable, $route->getParameters());
        return $this->toResponse($return);
    }

    /**
     * @param mixed $resp
     * @return Response
     */
    private function toResponse($resp) :Response
    {
        if ($resp instanceof Response) {
            $response = $resp;
        } else if (is_array($resp) || is_scalar($resp)) {
            $response = new Response($resp);
        } else {
            throw new ResponseException(500, 'invalid response');
        }
        return $response;
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