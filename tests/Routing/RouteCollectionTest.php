<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Routing;

use Tests\BaseTestCase;
use Zim\Routing\Route;
use Zim\Routing\RouteCollection;

class RouteCollectionTest extends BaseTestCase
{
    public function testRoute()
    {
        $collection = new RouteCollection();
        $route = new Route('/foo');
        $collection->add('foo', $route);
        $this->assertEquals(array('foo' => $route), $collection->all(), '->add() adds a route');
        $this->assertEquals($route, $collection->get('foo'), '->get() returns a route by name');
        $this->assertNull($collection->get('bar'), '->get() returns null if a route does not exist');
    }

    public function testOverriddenRoute()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));
        $collection->add('foo', new Route('/foo1'));

        $this->assertEquals('/foo1', $collection->get('foo')->getPath());
    }

    public function testDeepOverriddenRoute()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));

        $collection1 = new RouteCollection();
        $collection1->add('foo', new Route('/foo1'));

        $collection2 = new RouteCollection();
        $collection2->add('foo', new Route('/foo2'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);

        $this->assertEquals('/foo2', $collection1->get('foo')->getPath());
        $this->assertEquals('/foo2', $collection->get('foo')->getPath());
    }

    public function testIterator()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar'));
        $collection1->add('foo', $foo = new Route('/foo-new'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last'));

        $this->assertInstanceOf('\ArrayIterator', $collection->getIterator());
        $this->assertSame(array('bar' => $bar, 'foo' => $foo, 'last' => $last), $collection->getIterator()->getArrayCopy());
    }

    public function testCount()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', new Route('/bar'));
        $collection->addCollection($collection1);

        $this->assertCount(2, $collection);
    }

    public function testAddCollection()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar'));
        $collection1->add('foo', $foo = new Route('/foo-new'));

        $collection2 = new RouteCollection();
        $collection2->add('grandchild', $grandchild = new Route('/grandchild'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last'));

        $this->assertSame(array('bar' => $bar, 'foo' => $foo, 'grandchild' => $grandchild, 'last' => $last), $collection->all(),
            '->addCollection() imports routes of another collection, overrides if necessary and adds them at the end');
    }

    public function testAddDefaultsAndRequirementsAndOptions()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/{placeholder}'));
        $collection1 = new RouteCollection();
        $collection1->add('bar', new Route('/{placeholder}',
            array('_controller' => 'fixed', 'placeholder' => 'default'), array(), array('placeholder' => '.+'), array('option' => 'value'))
        );
        $collection->addCollection($collection1);

        $collection->addDefaults(array('placeholder' => 'new-default'));
        $this->assertEquals(array('placeholder' => 'new-default'), $collection->get('foo')->getDefaults(), '->addDefaults() adds defaults to all routes');
        $this->assertEquals(array('_controller' => 'fixed', 'placeholder' => 'new-default'), $collection->get('bar')->getDefaults(),
            '->addDefaults() adds defaults to all routes and overwrites existing ones');

        $collection->addRequirements(array('placeholder' => '\d+'));
        $this->assertEquals(array('placeholder' => '\d+'), $collection->get('foo')->getRequirements(), '->addRequirements() adds requirements to all routes');
        $this->assertEquals(array('placeholder' => '\d+'), $collection->get('bar')->getRequirements(),
            '->addRequirements() adds requirements to all routes and overwrites existing ones');

        $collection->addOptions(array('option' => 'new-value'));
        $this->assertEquals(
            array('option' => 'new-value'),
            $collection->get('bar')->getOptions(), '->addOptions() adds options to all routes and overwrites existing ones'
        );
    }

    public function testAddPrefix()
    {
        $collection = new RouteCollection();
        $collection->add('foo', $foo = new Route('/foo'));
        $collection2 = new RouteCollection();
        $collection2->add('bar', $bar = new Route('/bar'));
        $collection->addCollection($collection2);
        $collection->addPrefix(' / ');
        $this->assertSame('/foo', $collection->get('foo')->getPath(), '->addPrefix() trims the prefix and a single slash has no effect');
        $collection->addPrefix('/{admin}', array('admin' => 'admin'), array('admin' => '\d+'));
        $this->assertEquals('/{admin}/foo', $collection->get('foo')->getPath(), '->addPrefix() adds a prefix to all routes');
        $this->assertEquals('/{admin}/bar', $collection->get('bar')->getPath(), '->addPrefix() adds a prefix to all routes');
        $this->assertEquals(array('admin' => 'admin'), $collection->get('foo')->getDefaults(), '->addPrefix() adds defaults to all routes');
        $this->assertEquals(array('admin' => 'admin'), $collection->get('bar')->getDefaults(), '->addPrefix() adds defaults to all routes');
        $this->assertEquals(array('admin' => '\d+'), $collection->get('foo')->getRequirements(), '->addPrefix() adds requirements to all routes');
        $this->assertEquals(array('admin' => '\d+'), $collection->get('bar')->getRequirements(), '->addPrefix() adds requirements to all routes');
        $collection->addPrefix('0');
        $this->assertEquals('/0/{admin}/foo', $collection->get('foo')->getPath(), '->addPrefix() ensures a prefix must start with a slash and must not end with a slash');
        $collection->addPrefix('/ /');
        $this->assertSame('/ /0/{admin}/foo', $collection->get('foo')->getPath(), '->addPrefix() can handle spaces if desired');
        $this->assertSame('/ /0/{admin}/bar', $collection->get('bar')->getPath(), 'the route pattern of an added collection is in synch with the added prefix');
    }

    public function testAddPrefixOverridesDefaultsAndRequirements()
    {
        $collection = new RouteCollection();
        $collection->add('foo', $foo = new Route('/foo.{_format}'));
        $collection->add('bar', $bar = new Route('/bar.{_format}', array(), array('_format' => 'json')));
        $collection->addPrefix('/admin', array(), array('_format' => 'html'));

        $this->assertEquals('html', $collection->get('foo')->getRequirement('_format'), '->addPrefix() overrides existing requirements');
        $this->assertEquals('html', $collection->get('bar')->getRequirement('_format'), '->addPrefix() overrides existing requirements');
    }

    public function testUniqueRouteWithGivenName()
    {
        $collection1 = new RouteCollection();
        $collection1->add('foo', new Route('/old'));
        $collection2 = new RouteCollection();
        $collection3 = new RouteCollection();
        $collection3->add('foo', $new = new Route('/new'));

        $collection2->addCollection($collection3);
        $collection1->addCollection($collection2);

        $this->assertSame($new, $collection1->get('foo'), '->get() returns new route that overrode previous one');
        // size of 1 because collection1 contains /new but not /old anymore
        $this->assertCount(1, $collection1->getIterator(), '->addCollection() removes previous routes when adding new routes with the same name');
    }

    public function testGet()
    {
        $collection1 = new RouteCollection();
        $collection1->add('a', $a = new Route('/a'));
        $collection2 = new RouteCollection();
        $collection2->add('b', $b = new Route('/b'));
        $collection1->addCollection($collection2);
        $collection1->add('$péß^a|', $c = new Route('/special'));

        $this->assertSame($b, $collection1->get('b'), '->get() returns correct route in child collection');
        $this->assertSame($c, $collection1->get('$péß^a|'), '->get() can handle special characters');
        $this->assertNull($collection2->get('a'), '->get() does not return the route defined in parent collection');
        $this->assertNull($collection1->get('non-existent'), '->get() returns null when route does not exist');
        $this->assertNull($collection1->get(0), '->get() does not disclose internal child RouteCollection');
    }

    public function testRemove()
    {
        $collection = new RouteCollection();
        $collection->add('foo', $foo = new Route('/foo'));

        $collection1 = new RouteCollection();
        $collection1->add('bar', $bar = new Route('/bar'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Route('/last'));

        $collection->remove('foo');
        $this->assertSame(array('bar' => $bar, 'last' => $last), $collection->all(), '->remove() can remove a single route');
        $collection->remove(array('bar', 'last'));
        $this->assertSame(array(), $collection->all(), '->remove() accepts an array and can remove multiple routes at once');
    }

    public function testClone()
    {
        $collection = new RouteCollection();
        $collection->add('a', new Route('/a'));
        $collection->add('b', new Route('/b', array('placeholder' => 'default'), array('placeholder' => '.+')));

        $clonedCollection = clone $collection;

        $this->assertCount(2, $clonedCollection);
        $this->assertEquals($collection->get('a'), $clonedCollection->get('a'));
        $this->assertNotSame($collection->get('a'), $clonedCollection->get('a'));
        $this->assertEquals($collection->get('b'), $clonedCollection->get('b'));
        $this->assertNotSame($collection->get('b'), $clonedCollection->get('b'));
    }

    public function testSetMethods()
    {
        $collection = new RouteCollection();
        $routea = new Route('/a', array(),  array('GET', 'POST'));
        $routeb = new Route('/b');
        $collection->add('a', $routea);
        $collection->add('b', $routeb);

        $collection->setMethods('PUT');

        $this->assertEquals(array('PUT'), $routea->getMethods());
        $this->assertEquals(array('PUT'), $routeb->getMethods());
    }

    public function testAddNamePrefix()
    {
        $collection = new RouteCollection();
        $collection->add('foo', $foo = new Route('/foo'));
        $collection->add('bar', $bar = new Route('/bar'));
        $collection->add('api_foo', $apiFoo = new Route('/api/foo'));
        $collection->addNamePrefix('api_');

        $this->assertEquals($foo, $collection->get('api_foo'));
        $this->assertEquals($bar, $collection->get('api_bar'));
        $this->assertEquals($apiFoo, $collection->get('api_api_foo'));
        $this->assertNull($collection->get('foo'));
        $this->assertNull($collection->get('bar'));
    }

    public function testAddNamePrefixCanonicalRouteName()
    {
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', array('_canonical_route' => 'foo')));
        $collection->add('bar', new Route('/bar', array('_canonical_route' => 'bar')));
        $collection->add('api_foo', new Route('/api/foo', array('_canonical_route' => 'api_foo')));
        $collection->addNamePrefix('api_');

        $this->assertEquals('api_foo', $collection->get('api_foo')->getDefault('_canonical_route'));
        $this->assertEquals('api_bar', $collection->get('api_bar')->getDefault('_canonical_route'));
        $this->assertEquals('api_api_foo', $collection->get('api_api_foo')->getDefault('_canonical_route'));
    }
}
