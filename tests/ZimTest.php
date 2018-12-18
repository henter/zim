<?php
namespace Tests;

use Zim\Config\Config;
use Zim\Event\Dispatcher;
use Zim\Event\Event;
use Zim\Routing\Router;
use Zim\Zim;

/**
 * File Zim.php
 * @henter
 * Time: 2018-12-14 11:52
 *
 */

class ZimTest extends BaseTestCase
{
    public function testInstance()
    {
        $zim = \Zim\Zim::getInstance();
        $this->assertIsObject($zim);
    }

    public function testDefaultContainer()
    {
        $zim = \Zim\Zim::getInstance();

        $this->assertSame($zim, $zim->make('zim'));
        $this->assertSame($zim, $zim->make(\Zim\Zim::class));
        $this->assertSame($zim, $zim->make(\Zim\Container\Container::class));

        $this->assertInstanceOf(Config::class, $zim->make('config'));
        $this->assertInstanceOf(Config::class, $zim->make(Config::class));
        $this->assertInstanceOf(Config::class, $zim->make(\Zim\Contract\Config::class));
        $this->assertSame($zim::app('config'), $zim->make(Config::class));

        $this->assertInstanceOf(Router::class, $zim->make('router'));
        $this->assertInstanceOf(Router::class, $zim->make(Router::class));

        $this->assertInstanceOf(Dispatcher::class, $zim->make('event'));
        $this->assertInstanceOf(Dispatcher::class, $zim->make(Event::class));
        $this->assertInstanceOf(Dispatcher::class, $zim->make(Dispatcher::class));
    }

    public function testConfig()
    {
        $c = $this->getConfig('app');
        $this->assertEquals($c, Zim::config('app'));
    }

    public function testEvent()
    {
        $event = 'test.event';
        $payload = ['test event payload', 'ok', 123];
        Event::listen($event, function($e, $p) use ($event, $payload) {
            $this->assertEquals($event, $e);
            $this->assertEquals($payload, $p);
        });
        Event::fire($event, $payload);
        Zim::getInstance()->fire($event, $payload);
    }
}