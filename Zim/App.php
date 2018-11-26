<?php
/**
 * File App.php
 * @henter
 * Time: 2018-11-24 19:29
 *
 */
namespace Zim;

use Zim\Debug\ErrorHandler;
use Zim\Debug\ExceptionHandler;
use Zim\Event\Dispatcher;
use Zim\Event\Event;
use Zim\Event\RequestEvent;
use Zim\Http\Exception\NotFoundException;
use Zim\Http\Exception\ResponseException;
use Zim\Routing\Router;
use Zim\Http\Request;
use Zim\Http\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class App
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new Dispatcher();
        $this->router = new Router();
        $this->debug();
    }

    public function debug()
    {
        error_reporting(E_ALL);

        if (!\in_array(\PHP_SAPI, array('cli', 'phpdbg'), true)) {
            ini_set('display_errors', 0);
            ExceptionHandler::register()->setHandler(function($e){
                throw $e;
            });
        } elseif ((!filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN) || ini_get('error_log'))) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
        ErrorHandler::register();
    }

    /**
     * @param null $request
     * @throws \Throwable
     */
    public function run($request = null)
    {
        $this->dispatcher->dispatch(new RequestEvent($request));

        $response = $this->handle($request);
        $this->dispatcher->dispatch(Event::RESPONSE);

        $response->send();
        $this->terminate($request, $response);
    }

    public function terminate($request, $response)
    {
        $this->dispatcher->dispatch(Event::TERMINATE);
    }

    /**
     * @param  array $routeInfo
     * @return mixed|string|array|bool|Response
     */
    protected function callControllerMethod($routeInfo)
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
    public function callControllerAction(Request $request)
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
        $actionClass = (new $controllerClass)->getAction($a);
        if (!class_exists($actionClass)) {
            throw new NotFoundException('action not found');
        }

        try {
            $return = (new $actionClass)->execute();
        } catch (\Throwable $e) {
            throw $e;
        }
        return $return;
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
        } catch (ResourceNotFoundException $e) {
            $actionReturn = $this->callControllerAction($request);
        } catch (\Throwable $e) {
            return $this->errorResponse('exception '.$e->getMessage())->prepare($request);
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