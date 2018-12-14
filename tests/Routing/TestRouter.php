<?php
/**
 * File TestRouter.php
 * @henter
 * Time: 2018-12-14 17:10
 *
 */

namespace Tests\Routing;


use Tests\BaseTestCase;
use Zim\Routing\Router;

class TestRouter extends BaseTestCase
{

    public function testRouter()
    {
        $router = new Router();
        $router->addRoute('GET', '/test', ['name' => 'test']);
        $this->assertEquals(1, count($router->getRoutes()));
    }

}