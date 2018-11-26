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
     * 根据 uri 猜测 controller 类名
     * @param string $uri
     * @return string|bool
     */
    private function guessAppController($uri)
    {
        if (file_exists(APP_PATH.'/Controller/'.ucfirst($uri).'Controller.php')) {
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
        $segments = explode('/', $request->getPathInfo());
        if (count($segments) < 3) {
            throw new NotFoundException('path not found');
        }
        list(, $c, $a) = explode('/', $request->getPathInfo());

        //try controller
        if (!$c = $this->guessAppController($c)) {
            throw new NotFoundException('controller not found');
        }

        /**
         * @var Controller $controller
         */
        $controller = App::getInstance()->make('App\\Controller\\'.$c);

        //try controller method ?
        if ($method = $controller->getMethod($a)) {
            return call_user_func_array([$controller, $method], []);
        }

        //try controller action class
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