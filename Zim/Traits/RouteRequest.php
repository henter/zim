<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;

use Zim\Routing\RouteDependencyResolverTrait;
use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Http\Request;
use Zim\Http\Response;
use Zim\Http\Controller;
use Zim\Routing\Route;
use Zim\Routing\Router;

trait RouteRequest
{
    use RouteDependencyResolverTrait;

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
     * @param Route $route
     * @param $controller
     * @param $method
     * @return mixed|string|array|bool|Response
     */
    protected function callControllerAction(Route $route, $controller, $method)
    {
        $parameters = $this->resolveClassMethodDependencies($route->getParameters(), $controller, $method);

        return $controller->{$method}(...array_values($parameters));
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
     * TODO, united to callable return
     *
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

        //try controller action ?
        if ($method = $controller->getAction($a)) {
            $parameters = $this->resolveMethodDependencies([], new \ReflectionMethod($controller, $method));
            return $controller->{$method}(...array_values($parameters));
        }

        //try controller action class
        if (!class_exists($actionClass = $controller->getActionClass($a))) {
            throw new NotFoundException('action not found');
        }

        $action = $this->make($actionClass);
        $parameters = $this->resolveMethodDependencies([], new \ReflectionMethod($action, 'execute'));
        return $action->execute(...array_values($parameters));
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

        try {
            $requestEvent = new RequestEvent($request);
            $this->fire($requestEvent);

            if ($resp = $requestEvent->getResponse()) {
                return $resp->prepare($request);
            }

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
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function dispatchToDefault(Request $request) :Response
    {
        $return = $this->tryCallControllerAction($request);
        return $this->toResponse($return);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dispatchToRouter(Request $request) :Response
    {
        $route = $this->router->dispatch($request);
        $controller = $this->make($route->getDefault('_controller'));

        $return = $this->callControllerAction($route, $controller, $route->getDefault('_action'));
        return $this->toResponse($return);
    }

    /**
     * @param mixed $resp
     * @return Response
     */
    private function toResponse($resp)
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