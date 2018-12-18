<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use Zim\Routing\Route;

class RouteTest extends TestCase
{
    public function testConstructor()
    {
        $route = new Route('/{foo}', array('foo' => 'bar'), array('foo' => '\d+'), [], array('foo' => 'bar'));
        $this->assertEquals('/{foo}', $route->getPath(), '__construct() takes a path as its first argument');
        $this->assertEquals(array('foo' => 'bar'), $route->getDefaults(), '__construct() takes defaults as its second argument');
        $this->assertEquals(array('foo' => '\d+'), $route->getRequirements(), '__construct() takes requirements as its third argument');
        $this->assertEquals('bar', $route->getOption('foo'), '__construct() takes options as its fourth argument');

        $route = new Route('/', array(), array(), array('POST', 'put'), []);
        $this->assertEquals(array('POST', 'PUT'), $route->getMethods(), '__construct() takes methods as its seventh argument and uppercases it');
    }

    public function testPath()
    {
        $route = new Route('/{foo}');
        $route->setPath('/{bar}');
        $this->assertEquals('/{bar}', $route->getPath(), '->setPath() sets the path');
        $route->setPath('');
        $this->assertEquals('/', $route->getPath(), '->setPath() adds a / at the beginning of the path if needed');
        $route->setPath('bar');
        $this->assertEquals('/bar', $route->getPath(), '->setPath() adds a / at the beginning of the path if needed');
        $this->assertEquals($route, $route->setPath(''), '->setPath() implements a fluent interface');
        $route->setPath('//path');
        $this->assertEquals('/path', $route->getPath(), '->setPath() does not allow two slashes "//" at the beginning of the path as it would be confused with a network path when generating the path from the route');
    }

    public function testOptions()
    {
        $route = new Route('/{foo}');
        $route->addOptions(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $route->getOptions(), '->setOptions() sets the options');
        $this->assertEquals($route, $route->addOptions(array()), '->setOptions() implements a fluent interface');

        $route->addOptions(array('foo' => 'foo'));
        $route->addOptions(array('bar' => 'bar'));
        $this->assertEquals($route, $route->addOptions(array()), '->addOptions() implements a fluent interface');
        $this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $route->getOptions(), '->addDefaults() keep previous defaults');
    }

    public function testOption()
    {
        $route = new Route('/{foo}');
        $this->assertFalse($route->hasOption('foo'), '->hasOption() return false if option is not set');
        $this->assertEquals($route, $route->setOption('foo', 'bar'), '->setOption() implements a fluent interface');
        $this->assertEquals('bar', $route->getOption('foo'), '->setOption() sets the option');
        $this->assertTrue($route->hasOption('foo'), '->hasOption() return true if option is set');
    }

    public function testDefaults()
    {
        $route = new Route('/{foo}');
        $route->setDefaults(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $route->getDefaults(), '->setDefaults() sets the defaults');
        $this->assertEquals($route, $route->setDefaults(array()), '->setDefaults() implements a fluent interface');

        $route->setDefault('foo', 'bar');
        $this->assertEquals('bar', $route->getDefault('foo'), '->setDefault() sets a default value');

        $route->setDefault('foo2', 'bar2');
        $this->assertEquals('bar2', $route->getDefault('foo2'), '->getDefault() return the default value');
        $this->assertNull($route->getDefault('not_defined'), '->getDefault() return null if default value is not set');

        $route->setDefault('_controller', $closure = function () { return 'Hello'; });
        $this->assertEquals($closure, $route->getDefault('_controller'), '->setDefault() sets a default value');

        $route->setDefaults(array('foo' => 'foo'));
        $route->addDefaults(array('bar' => 'bar'));
        $this->assertEquals($route, $route->addDefaults(array()), '->addDefaults() implements a fluent interface');
        $this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $route->getDefaults(), '->addDefaults() keep previous defaults');
    }

    public function testRequirements()
    {
        $route = new Route('/{foo}');
        $route->setRequirements(array('foo' => '\d+'));
        $this->assertEquals(array('foo' => '\d+'), $route->getRequirements(), '->setRequirements() sets the requirements');
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->getRequirement() returns a requirement');
        $this->assertNull($route->getRequirement('bar'), '->getRequirement() returns null if a requirement is not defined');
        $route->setRequirements(array('foo' => '^\d+$'));
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->getRequirement() removes ^ and $ from the path');
        $this->assertEquals($route, $route->setRequirements(array()), '->setRequirements() implements a fluent interface');

        $route->setRequirements(array('foo' => '\d+'));
        $route->addRequirements(array('bar' => '\d+'));
        $this->assertEquals($route, $route->addRequirements(array()), '->addRequirements() implements a fluent interface');
        $this->assertEquals(array('foo' => '\d+', 'bar' => '\d+'), $route->getRequirements(), '->addRequirement() keep previous requirements');
    }

    public function testRequirement()
    {
        $route = new Route('/{foo}');
        $this->assertFalse($route->hasRequirement('foo'), '->hasRequirement() return false if requirement is not set');
        $route->setRequirement('foo', '^\d+$');
        $this->assertEquals('\d+', $route->getRequirement('foo'), '->setRequirement() removes ^ and $ from the path');
        $this->assertTrue($route->hasRequirement('foo'), '->hasRequirement() return true if requirement is set');
    }

    /**
     * @dataProvider getInvalidRequirements
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidRequirement($req)
    {
        $route = new Route('/{foo}');
        $route->setRequirement('foo', $req);
    }

    public function getInvalidRequirements()
    {
        return array(
           array(''),
           array('^$'),
           array('^'),
           array('$'),
        );
    }

    public function testMethod()
    {
        $route = new Route('/');
        $this->assertEquals(array(), $route->getMethods(), 'methods is initialized with array()');
        $route->setMethods(['gEt']);
        $this->assertEquals(array('GET'), $route->getMethods(), '->setMethods() accepts a single method string and uppercases it');
        $route->setMethods(array('gEt', 'PosT'));
        $this->assertEquals(array('GET', 'POST'), $route->getMethods(), '->setMethods() accepts an array of methods and uppercases them');
    }

    public function testCompile()
    {
        $route = new Route('/{foo}');
        $this->assertInstanceOf('Zim\Routing\CompiledRoute', $compiled = $route->compile(), '->compile() returns a compiled route');
        $this->assertSame($compiled, $route->compile(), '->compile() only compiled the route once if unchanged');
        $route->setRequirement('foo', '.*');
        $this->assertNotSame($compiled, $route->compile(), '->compile() recompiles if the route was modified');
    }

    public function testSerialize()
    {
        $route = new Route('/prefix/{foo}', array('foo' => 'default'), array('foo' => '\d+'));

        $serialized = serialize($route);
        $unserialized = unserialize($serialized);

        $this->assertEquals($route, $unserialized);
        $this->assertNotSame($route, $unserialized);
    }

    public function testInlineDefaultAndRequirement()
    {
        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', null), new Route('/foo/{bar?}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', 'baz'), new Route('/foo/{bar?baz}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', 'baz<buz>'), new Route('/foo/{bar?baz<buz>}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', 'baz'), new Route('/foo/{bar?}', array('bar' => 'baz')));

        $this->assertEquals((new Route('/foo/{bar}'))->setRequirement('bar', '.*'), new Route('/foo/{bar<.*>}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setRequirement('bar', '>'), new Route('/foo/{bar<>>}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setRequirement('bar', '\d+'), new Route('/foo/{bar<.*>}', array(), array('bar' => '\d+')));
        $this->assertEquals((new Route('/foo/{bar}'))->setRequirement('bar', '[a-z]{2}'), new Route('/foo/{bar<[a-z]{2}>}'));

        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', null)->setRequirement('bar', '.*'), new Route('/foo/{bar<.*>?}'));
        $this->assertEquals((new Route('/foo/{bar}'))->setDefault('bar', '<>')->setRequirement('bar', '>'), new Route('/foo/{bar<>>?<>}'));
    }

}
