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
use Zim\Routing\RouteCompiler;

class RouteCompilerTest extends TestCase
{
    /**
     * @param $arguments
     * @return Route
     */
    public static function newRoute($arguments)
    {
        return new Route(...$arguments);
    }

    /**
     * @dataProvider provideCompileData
     */
    public function testCompile($name, $arguments, $prefix, $regex, $variables, $tokens)
    {
        $compiled = self::newRoute($arguments)->compile();
        $this->assertEquals($prefix, $compiled->getStaticPrefix(), $name.' (static prefix)');
        $this->assertEquals($regex, $compiled->getRegex(), $name.' (regex)');
        $this->assertEquals($variables, $compiled->getVariables(), $name.' (variables)');
        $this->assertEquals($tokens, $compiled->getTokens(), $name.' (tokens)');
    }

    public function provideCompileData()
    {
        return array(
            array(
                'Static route',
                array('/foo'),
                '/foo', '#^/foo$#sD', array(), array(
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with a variable',
                array('/foo/{bar}'),
                '/foo', '#^/foo/(?P<bar>[^/]++)$#sD', array('bar'), array(
                array('variable', '/', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with a variable that has a default value',
                array('/foo/{bar}', array('bar' => 'bar')),
                '/foo', '#^/foo(?:/(?P<bar>[^/]++))?$#sD', array('bar'), array(
                array('variable', '/', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with several variables',
                array('/foo/{bar}/{foobar}'),
                '/foo', '#^/foo/(?P<bar>[^/]++)/(?P<foobar>[^/]++)$#sD', array('bar', 'foobar'), array(
                array('variable', '/', '[^/]++', 'foobar'),
                array('variable', '/', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with several variables that have default values',
                array('/foo/{bar}/{foobar}', array('bar' => 'bar', 'foobar' => '')),
                '/foo', '#^/foo(?:/(?P<bar>[^/]++)(?:/(?P<foobar>[^/]++))?)?$#sD', array('bar', 'foobar'), array(
                array('variable', '/', '[^/]++', 'foobar'),
                array('variable', '/', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with several variables but some of them have no default values',
                array('/foo/{bar}/{foobar}', array('bar' => 'bar')),
                '/foo', '#^/foo/(?P<bar>[^/]++)/(?P<foobar>[^/]++)$#sD', array('bar', 'foobar'), array(
                array('variable', '/', '[^/]++', 'foobar'),
                array('variable', '/', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with an optional variable as the first segment',
                array('/{bar}', array('bar' => 'bar')),
                '', '#^/(?P<bar>[^/]++)?$#sD', array('bar'), array(
                array('variable', '/', '[^/]++', 'bar'),
            ),
            ),

            array(
                'Route with a requirement of 0',
                array('/{bar}', array('bar' => null), array('bar' => '0')),
                '', '#^/(?P<bar>0)?$#sD', array('bar'), array(
                array('variable', '/', '0', 'bar'),
            ),
            ),

            array(
                'Route with an optional variable as the first segment with requirements',
                array('/{bar}', array('bar' => 'bar'), array('bar' => '(foo|bar)')),
                '', '#^/(?P<bar>(?:foo|bar))?$#sD', array('bar'), array(
                array('variable', '/', '(?:foo|bar)', 'bar'),
            ),
            ),

            array(
                'Route with only optional variables',
                array('/{foo}/{bar}', array('foo' => 'foo', 'bar' => 'bar')),
                '', '#^/(?P<foo>[^/]++)?(?:/(?P<bar>[^/]++))?$#sD', array('foo', 'bar'), array(
                array('variable', '/', '[^/]++', 'bar'),
                array('variable', '/', '[^/]++', 'foo'),
            ),
            ),

            array(
                'Route with a variable in last position',
                array('/foo-{bar}'),
                '/foo-', '#^/foo\-(?P<bar>[^/]++)$#sD', array('bar'), array(
                array('variable', '-', '[^/]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Route with nested placeholders',
                array('/{static{var}static}'),
                '/{static', '#^/\{static(?P<var>[^/]+)static\}$#sD', array('var'), array(
                array('text', 'static}'),
                array('variable', '', '[^/]+', 'var'),
                array('text', '/{static'),
            ),
            ),

            array(
                'Route without separator between variables',
                array('/{w}{x}{y}{z}.{_format}', array('z' => 'default-z', '_format' => 'html'), array('y' => '(y|Y)')),
                '', '#^/(?P<w>[^/\.]+)(?P<x>[^/\.]+)(?P<y>(?:y|Y))(?:(?P<z>[^/\.]++)(?:\.(?P<_format>[^/]++))?)?$#sD', array('w', 'x', 'y', 'z', '_format'), array(
                array('variable', '.', '[^/]++', '_format'),
                array('variable', '', '[^/\.]++', 'z'),
                array('variable', '', '(?:y|Y)', 'y'),
                array('variable', '', '[^/\.]+', 'x'),
                array('variable', '/', '[^/\.]+', 'w'),
            ),
            ),

            array(
                'Route with a format',
                array('/foo/{bar}.{_format}'),
                '/foo', '#^/foo/(?P<bar>[^/\.]++)\.(?P<_format>[^/]++)$#sD', array('bar', '_format'), array(
                array('variable', '.', '[^/]++', '_format'),
                array('variable', '/', '[^/\.]++', 'bar'),
                array('text', '/foo'),
            ),
            ),

            array(
                'Static non UTF-8 route',
                array("/fo\xE9"),
                "/fo\xE9", "#^/fo\xE9$#sD", array(), array(
                array('text', "/fo\xE9"),
            ),
            ),

            array(
                'Route with an explicit UTF-8 requirement',
                array('/{bar}', array('bar' => null), array('bar' => '.'), [], array('utf8' => true)),
                '', '#^/(?P<bar>.)?$#sDu', array('bar'), array(
                array('variable', '/', '.', 'bar', true),
            ),
            ),
        );
    }

    /**
     * @dataProvider provideCompileImplicitUtf8Data
     * @expectedException \LogicException
     */
    public function testCompileImplicitUtf8Data($name, $arguments, $prefix, $regex, $variables, $tokens, $deprecationType)
    {
        $compiled = self::newRoute($arguments)->compile();
        $this->assertEquals($prefix, $compiled->getStaticPrefix(), $name.' (static prefix)');
        $this->assertEquals($regex, $compiled->getRegex(), $name.' (regex)');
        $this->assertEquals($variables, $compiled->getVariables(), $name.' (variables)');
        $this->assertEquals($tokens, $compiled->getTokens(), $name.' (tokens)');
    }

    public function provideCompileImplicitUtf8Data()
    {
        return array(
            array(
                'Static UTF-8 route',
                array('/foé'),
                '/foé', '#^/foé$#sDu', array(), array(
                array('text', '/foé'),
            ),
                'patterns',
            ),

            array(
                'Route with an implicit UTF-8 requirement',
                array('/{bar}', array('bar' => null), array('bar' => 'é')),
                '', '#^/(?P<bar>é)?$#sDu', array('bar'), array(
                array('variable', '/', 'é', 'bar', true),
            ),
                'requirements',
            ),

            array(
                'Route with a UTF-8 class requirement',
                array('/{bar}', array('bar' => null), array('bar' => '\pM')),
                '', '#^/(?P<bar>\pM)?$#sDu', array('bar'), array(
                array('variable', '/', '\pM', 'bar', true),
            ),
                'requirements',
            ),
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRouteWithSameVariableTwice()
    {
        $route = new Route('/{name}/{name}');

        $compiled = $route->compile();
    }

    /**
     * @expectedException \LogicException
     */
    public function testRouteCharsetMismatch()
    {
        $route = new Route("/\xE9/{bar}", array(), array('bar' => '.'), [], array('utf8' => true));

        $compiled = $route->compile();
    }

    /**
     * @expectedException \LogicException
     */
    public function testRequirementCharsetMismatch()
    {
        $route = new Route('/foo/{bar}', array(), array('bar' => "\xE9"), [], array('utf8' => true));

        $compiled = $route->compile();
    }

    /**
     * @dataProvider getVariableNamesStartingWithADigit
     * @expectedException \DomainException
     */
    public function testRouteWithVariableNameStartingWithADigit($name)
    {
        $route = new Route('/{'.$name.'}');
        $route->compile();
    }

    public function getVariableNamesStartingWithADigit()
    {
        return array(
            array('09'),
            array('123'),
            array('1e2'),
        );
    }


    /**
     * @expectedException \DomainException
     */
    public function testRouteWithTooLongVariableName()
    {
        $route = new Route(sprintf('/{%s}', str_repeat('a', RouteCompiler::VARIABLE_MAXIMUM_LENGTH + 1)));
        $route->compile();
    }

    /**
     * @dataProvider provideRemoveCapturingGroup
     */
    public function testRemoveCapturingGroup($regex, $requirement)
    {
        $route = new Route('/{foo}', array(), array('foo' => $requirement));

        $this->assertSame($regex, $route->compile()->getRegex());
    }

    public function provideRemoveCapturingGroup()
    {
        yield array('#^/(?P<foo>a(?:b|c)(?:d|e)f)$#sD', 'a(b|c)(d|e)f');
        yield array('#^/(?P<foo>a\(b\)c)$#sD', 'a\(b\)c');
        yield array('#^/(?P<foo>(?:b))$#sD', '(?:b)');
        yield array('#^/(?P<foo>(?(b)b))$#sD', '(?(b)b)');
        yield array('#^/(?P<foo>(*F))$#sD', '(*F)');
        yield array('#^/(?P<foo>(?:(?:foo)))$#sD', '((foo))');
    }
}
