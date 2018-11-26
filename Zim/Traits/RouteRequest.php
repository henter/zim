<?php
/**
 * File RouteRequest.php
 * @henter
 * Time: 2018-11-26 16:07
 *
 */

namespace Zim\Traits;

use Zim\App;
use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Http\Request;
use Zim\Http\Response;
use Zim\Routing\Controller;

trait RouteRequest
{

    /**
     * @param null $request
     * @throws \Throwable
     */
    public function run($request = null)
    {
        App::dispatch(new RequestEvent($request));
        $response = $this->handle($request);
        App::dispatch(Event::RESPONSE);
        $response->send();
        App::dispatch(Event::TERMINATE);
    }

    /**
     * @param  array $routeInfo
     * @return mixed|string|array|bool|Response
     */
    protected function callControllerAction($routeInfo)
    {
        $instance = new $routeInfo['_controller'];

        if (!method_exists($instance, $routeInfo['_action'])) {
            return '404 not found method';
        }

        return call_user_func_array([$instance, $routeInfo['_action']], ['xx']);
    }

    /**
     * TODO controller驼峰命名时可能异常
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function tryCallControllerAction(Request $request)
    {
        $segments = explode('/', $request->getPathInfo());
        if (count($segments) < 3) {
            throw new NotFoundException('path not found');
        }
        list(, $c, $a) = explode('/', $request->getPathInfo());
        $controllerClass = 'App\\Controller\\'.ucfirst($c).'Controller';
        if (!class_exists($controllerClass)) {
            throw new NotFoundException('controller not found');
        }

        /**
         * @var Controller $controller
         */
        $controller = App::getInstance()->make($controllerClass);

        //check action method, support camelcase action name like someCamelCaseAction
        $methods = array_map('strtolower', get_class_methods($controller));
        if (in_array($a.'action', $methods)) {
            return call_user_func_array([$controller, $a.'Action'], []);
        }

        //check action file
        $actionClass = $controller->getAction($a);
        if (!class_exists($actionClass)) {
            throw new NotFoundException('action not found');
        }

        return App::getInstance()->make($actionClass)->execute();
    }

    /**
     * @param Request|null $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request = null)
    {
        $request = $request ?: Request::createFromGlobals();

        try {
            $routeInfo = $this->router->match($request->getPathInfo());
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
        return $response->prepare($request);
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