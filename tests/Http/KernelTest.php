<?php
/**
 * File KernelTest.php
 * @henter
 * Time: 2018-12-14 17:05
 *
 */

namespace Tests\Http;


use Tests\BaseTestCase;
use Zim\Config\Config;
use Zim\Event\RequestEvent;
use Zim\Event\ResponseEvent;
use Zim\Http\JsonResponse;
use Zim\Http\Kernel;
use Zim\Http\Request;
use Zim\Routing\Route;
use Zim\Routing\Router;

class KernelTest extends BaseTestCase
{
    public function testHandle()
    {
        $testContent = 'test response content';

        //remove event listener to avoid response hijack
        $this->getEvent()->remove(RequestEvent::class);
        $this->getEvent()->remove(ResponseEvent::class);

        /**
         * @var Kernel $http
         */
        $http = $this->zim->make(\Zim\Http\Kernel::class);
        $this->assertInstanceOf(Kernel::class, $http);

        /**
         * @var Router $router
         */
        $router = $this->zim->make('router');
        $this->assertInstanceOf(Router::class, $router);

//        $router->addRoute('POST', '/test_route1/{page<\d+>?123}', function($page) use ($testContent){
//            return $testContent;
//        });

        //test method not allowed
        try {
            $request = $this->getRequest('GET', '/test_route1/333');
            $response = $http->handle($request);
        } catch (\Exception $e) {
            $this->assertEquals('Allowed methods POST', $e->getMessage());
        }
        return;

        //test default page
        $router->addRoute('POST', '/test_route2/{page<\d+>?123}', function($page) use ($testContent){
            $this->assertEquals('123', $page);
            return $testContent;
        });
        $request = $this->getRequest('POST', '/test_route2');
        $response = $http->handle($request);
        $this->assertEquals($this->zim->get('request'), $request);
        $this->assertEquals($testContent, $response->getContent());

        //test custom page
        $router->addRoute('POST', '/test_route3/{page<\d+>?123}', function($page) use ($testContent){
            $this->assertEquals('345', $page);
            return $testContent;
        });
        $request = $this->getRequest('POST', '/test_route3/345');
        $response = $http->handle($request);
        $this->assertEquals($this->zim->get('request'), $request);
        $this->assertEquals($testContent, $response->getContent());

        //test callable
        $router->addRoute('POST', '/test_route4/{page<\d+>?123}', [$this, 'callableForRoute']);
        $request = $this->getRequest('POST', '/test_route4/345');
        $response = $http->handle($request);
        $this->assertEquals($this->zim->get('request'), $request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['data' => 'ok'], $response->getData());
    }

    public function callableForRoute($page = 2, \Zim\Contract\Config $config = null)
    {
        $c = require dirname(APP_PATH).'/config/app.php';
        $this->assertEquals($c, $config->get('app'));
        return ['data' => 'ok'];
    }

    public function getRequest($method, $uri)
    {
        $query = $request = $attr = $server = [];
        $server['REQUEST_METHOD'] = $method;
        $server['REQUEST_URI'] = $uri;
        return new Request($query, $request, $attr, $server);
    }

}