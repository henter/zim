<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Zim\Http\Request;

class RequestTest extends TestCase
{

    public function testInitialize()
    {
        $request = new Request();

        $request->initialize(array('foo' => 'bar'));
        $this->assertEquals('bar', $request->query->get('foo'), '->initialize() takes an array of query parameters as its first argument');

        $request->initialize(array(), array('foo' => 'bar'));
        $this->assertEquals('bar', $request->request->get('foo'), '->initialize() takes an array of request parameters as its second argument');

        $request->initialize(array(), array(), array('foo' => 'bar'));
        $this->assertEquals('bar', $request->attributes->get('foo'), '->initialize() takes an array of attributes as its third argument');

        $request->initialize(array(), array(), array(), array('HTTP_FOO' => 'bar'));
        $this->assertEquals('bar', $request->headers->get('FOO'), '->initialize() takes an array of HTTP headers as its sixth argument');
    }

    public function testIsNoCache()
    {
        $request = new Request();
        $isNoCache = $request->isNoCache();

        $this->assertFalse($isNoCache);
    }

    public function testGetContentType()
    {
        $request = new Request();
        $contentType = $request->getContentType();

        $this->assertNull($contentType);
    }

    public function testCreate()
    {
        $request = Request::create('http://test.com/foo?bar=baz');
        $this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('bar=baz', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com/foo', 'GET', array('bar' => 'baz'));
        $this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('bar=baz', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com/foo?bar=foo', 'GET', array('bar' => 'baz'));
        $this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('bar=baz', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('https://test.com/foo?bar=baz');
        $this->assertEquals('https://test.com/foo?bar=baz', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('bar=baz', $request->getQueryString());
        $this->assertEquals(443, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertTrue($request->isSecure());

        $request = Request::create('test.com:90/foo');
        $this->assertEquals('http://test.com:90/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com:90', $request->getHttpHost());
        $this->assertEquals(90, $request->getPort());
        $this->assertFalse($request->isSecure());

        $request = Request::create('https://test.com:90/foo');
        $this->assertEquals('https://test.com:90/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com:90', $request->getHttpHost());
        $this->assertEquals(90, $request->getPort());
        $this->assertTrue($request->isSecure());

        $request = Request::create('https://127.0.0.1:90/foo');
        $this->assertEquals('https://127.0.0.1:90/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('127.0.0.1', $request->getHost());
        $this->assertEquals('127.0.0.1:90', $request->getHttpHost());
        $this->assertEquals(90, $request->getPort());
        $this->assertTrue($request->isSecure());

        $request = Request::create('https://[::1]:90/foo');
        $this->assertEquals('https://[::1]:90/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('[::1]', $request->getHost());
        $this->assertEquals('[::1]:90', $request->getHttpHost());
        $this->assertEquals(90, $request->getPort());
        $this->assertTrue($request->isSecure());

        $request = Request::create('https://[::1]/foo');
        $this->assertEquals('https://[::1]/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('[::1]', $request->getHost());
        $this->assertEquals('[::1]', $request->getHttpHost());
        $this->assertEquals(443, $request->getPort());
        $this->assertTrue($request->isSecure());

        $json = '{"jsonrpc":"2.0","method":"echo","id":7,"params":["Hello World"]}';
        $request = Request::create('http://example.com/jsonrpc', 'POST', array(), array(), $json);
        $this->assertEquals($json, $request->getContent());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com');
        $this->assertEquals('http://test.com/', $request->getUri());
        $this->assertEquals('/', $request->getPathInfo());
        $this->assertEquals('', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com?test=1');
        $this->assertEquals('http://test.com/?test=1', $request->getUri());
        $this->assertEquals('/', $request->getPathInfo());
        $this->assertEquals('test=1', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com:90/?test=1');
        $this->assertEquals('http://test.com:90/?test=1', $request->getUri());
        $this->assertEquals('/', $request->getPathInfo());
        $this->assertEquals('test=1', $request->getQueryString());
        $this->assertEquals(90, $request->getPort());
        $this->assertEquals('test.com:90', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://username:password@test.com');
        $this->assertEquals('http://test.com/', $request->getUri());
        $this->assertEquals('/', $request->getPathInfo());
        $this->assertEquals('', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertEquals('username', $request->getUser());
        $this->assertEquals('password', $request->getPassword());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://username@test.com');
        $this->assertEquals('http://test.com/', $request->getUri());
        $this->assertEquals('/', $request->getPathInfo());
        $this->assertEquals('', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertEquals('username', $request->getUser());
        $this->assertSame('', $request->getPassword());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com/?foo');
        $this->assertEquals('/?foo', $request->getRequestUri());
        $this->assertEquals(array('foo' => ''), $request->query->all());

        // assume rewrite rule: (.*) --> app/app.php; app/ is a symlink to a symfony web/ directory
        $request = Request::create('http://test.com/apparthotel-1234', 'GET', array(),
            array(
                'DOCUMENT_ROOT' => '/var/www/www.test.com',
                'SCRIPT_FILENAME' => '/var/www/www.test.com/app/app.php',
                'SCRIPT_NAME' => '/app/app.php',
                'PHP_SELF' => '/app/app.php/apparthotel-1234',
            ));
        $this->assertEquals('http://test.com/apparthotel-1234', $request->getUri());
        $this->assertEquals('/apparthotel-1234', $request->getPathInfo());
        $this->assertEquals('', $request->getQueryString());
        $this->assertEquals(80, $request->getPort());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertFalse($request->isSecure());

        // Fragment should not be included in the URI
        $request = Request::create('http://test.com/foo#bar');
        $this->assertEquals('http://test.com/foo', $request->getUri());
    }

    public function testCreateWithRequestUri()
    {
        $request = Request::create('http://test.com:80/foo');
        $request->server->set('REQUEST_URI', 'http://test.com:80/foo');
        $this->assertEquals('http://test.com/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertEquals(80, $request->getPort());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com:8080/foo');
        $request->server->set('REQUEST_URI', 'http://test.com:8080/foo');
        $this->assertEquals('http://test.com:8080/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com:8080', $request->getHttpHost());
        $this->assertEquals(8080, $request->getPort());
        $this->assertFalse($request->isSecure());

        $request = Request::create('http://test.com/foo?bar=foo', 'GET', array('bar' => 'baz'));
        $request->server->set('REQUEST_URI', 'http://test.com/foo?bar=foo');
        $this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('bar=baz', $request->getQueryString());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertEquals(80, $request->getPort());
        $this->assertFalse($request->isSecure());

        $request = Request::create('https://test.com:443/foo');
        $request->server->set('REQUEST_URI', 'https://test.com:443/foo');
        $this->assertEquals('https://test.com/foo', $request->getUri());
        $this->assertEquals('/foo', $request->getPathInfo());
        $this->assertEquals('test.com', $request->getHost());
        $this->assertEquals('test.com', $request->getHttpHost());
        $this->assertEquals(443, $request->getPort());
        $this->assertTrue($request->isSecure());

        // Fragment should not be included in the URI
        $request = Request::create('http://test.com/foo#bar');
        $request->server->set('REQUEST_URI', 'http://test.com/foo#bar');
        $this->assertEquals('http://test.com/foo', $request->getUri());
    }

    public function testCreateCheckPrecedence()
    {
        // server is used by default
        $request = Request::create('/', 'DELETE', array(), array(
            'HTTP_HOST' => 'example.com',
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
            'PHP_AUTH_USER' => 'fabien',
            'PHP_AUTH_PW' => 'pa$$',
            'QUERY_STRING' => 'foo=bar',
            'CONTENT_TYPE' => 'application/json',
        ));
        $this->assertEquals('example.com', $request->getHost());
        $this->assertEquals(443, $request->getPort());
        $this->assertTrue($request->isSecure());
        $this->assertEquals('fabien', $request->getUser());
        $this->assertEquals('pa$$', $request->getPassword());
        $this->assertEquals('', $request->getQueryString());
        $this->assertEquals('application/json', $request->headers->get('CONTENT_TYPE'));

        // URI has precedence over server
        $request = Request::create('http://thomas:pokemon@example.net:8080/?foo=bar', 'GET', array(), array(
            'HTTP_HOST' => 'example.com',
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
        ));
        $this->assertEquals('example.net', $request->getHost());
        $this->assertEquals(8080, $request->getPort());
        $this->assertFalse($request->isSecure());
        $this->assertEquals('thomas', $request->getUser());
        $this->assertEquals('pokemon', $request->getPassword());
        $this->assertEquals('foo=bar', $request->getQueryString());
    }

    public function testDuplicate()
    {
        $request = new Request(array('foo' => 'bar'), array('foo' => 'bar'), array('foo' => 'bar'), array('HTTP_FOO' => 'bar'));
        $dup = $request->duplicate();

        $this->assertEquals($request->query->all(), $dup->query->all(), '->duplicate() duplicates a request an copy the current query parameters');
        $this->assertEquals($request->request->all(), $dup->request->all(), '->duplicate() duplicates a request an copy the current request parameters');
        $this->assertEquals($request->attributes->all(), $dup->attributes->all(), '->duplicate() duplicates a request an copy the current attributes');
        $this->assertEquals($request->headers->all(), $dup->headers->all(), '->duplicate() duplicates a request an copy the current HTTP headers');

        $dup = $request->duplicate(array('foo' => 'foobar'), array('foo' => 'foobar'), array('foo' => 'foobar'), array('HTTP_FOO' => 'foobar'));

        $this->assertEquals(array('foo' => 'foobar'), $dup->query->all(), '->duplicate() overrides the query parameters if provided');
        $this->assertEquals(array('foo' => 'foobar'), $dup->request->all(), '->duplicate() overrides the request parameters if provided');
        $this->assertEquals(array('foo' => 'foobar'), $dup->attributes->all(), '->duplicate() overrides the attributes if provided');
        $this->assertEquals(array('foo' => array('foobar')), $dup->headers->all(), '->duplicate() overrides the HTTP header if provided');
    }

    public function testDuplicateWithFormat()
    {
        $request = new Request(array(), array(), array('_format' => 'json'));
        $dup = $request->duplicate();

        $this->assertEquals('json', $dup->getRequestFormat());
        $this->assertEquals('json', $dup->attributes->get('_format'));

        $request = new Request();
        $request->setRequestFormat('xml');
        $dup = $request->duplicate();

        $this->assertEquals('xml', $dup->getRequestFormat());
    }

    /**
     * @dataProvider getFormatToMimeTypeMapProviderWithAdditionalNullFormat
     */
    public function testGetFormatFromMimeType($format, $mimeTypes)
    {
        $request = new Request();
        foreach ($mimeTypes as $mime) {
            $this->assertEquals($format, $request->getFormat($mime));
        }
        $request->setFormat($format, $mimeTypes);
        foreach ($mimeTypes as $mime) {
            $this->assertEquals($format, $request->getFormat($mime));

            if (null !== $format) {
                $this->assertEquals($mimeTypes[0], $request->getMimeType($format));
            }
        }
    }

    public function getFormatToMimeTypeMapProviderWithAdditionalNullFormat()
    {
        return array_merge(
            array(array('', array('', 'unexistent-mime-type'))),
            $this->getFormatToMimeTypeMapProvider()
        );
    }

    public function testGetFormatFromMimeTypeWithParameters()
    {
        $request = new Request();
        $this->assertEquals('json', $request->getFormat('application/json; charset=utf-8'));
        $this->assertEquals('json', $request->getFormat('application/json;charset=utf-8'));
        $this->assertEquals('json', $request->getFormat('application/json ; charset=utf-8'));
        $this->assertEquals('json', $request->getFormat('application/json ;charset=utf-8'));
    }

    /**
     * @dataProvider getFormatToMimeTypeMapProvider
     */
    public function testGetMimeTypeFromFormat($format, $mimeTypes)
    {
        $request = new Request();
        $this->assertEquals($mimeTypes[0], $request->getMimeType($format));
    }

    /**
     * @dataProvider getFormatToMimeTypeMapProvider
     */
    public function testGetMimeTypesFromFormat($format, $mimeTypes)
    {
        $this->assertEquals($mimeTypes, Request::getMimeTypes($format));
    }

    public function testGetMimeTypesFromInexistentFormat()
    {
        $request = new Request();
        $this->assertNull($request->getMimeType('foo'));
        $this->assertEquals(array(), Request::getMimeTypes('foo'));
    }

    public function testGetFormatWithCustomMimeType()
    {
        $request = new Request();
        $request->setFormat('custom', 'application/vnd.foo.api;myversion=2.3');
        $this->assertEquals('custom', $request->getFormat('application/vnd.foo.api;myversion=2.3'));
    }

    public function getFormatToMimeTypeMapProvider()
    {
        return array(
            array('txt', array('text/plain')),
            array('js', array('application/javascript', 'application/x-javascript', 'text/javascript')),
            array('css', array('text/css')),
            array('json', array('application/json', 'application/x-json')),
            array('jsonld', array('application/ld+json')),
            array('xml', array('text/xml', 'application/xml', 'application/x-xml')),
            array('rdf', array('application/rdf+xml')),
            array('atom', array('application/atom+xml')),
        );
    }

    public function testGetUri()
    {
        $server = array();

        // Standard Request on non default PORT
        // http://host:8080/index.php/path/info?query=string

        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/index.php/path/info?query=string';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PATH_INFO'] = '/path/info';
        $server['PATH_TRANSLATED'] = 'redirect:/index.php/path/info';
        $server['PHP_SELF'] = '/index_dev.php/path/info';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request = new Request();

        $request->initialize(array(), array(),  array(), $server);

        $this->assertEquals('http://host:8080/index.php/path/info?query=string', $request->getUri(), '->getUri() with non default port');

        // Use std port number
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('http://host/index.php/path/info?query=string', $request->getUri(), '->getUri() with default port');

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('http://servername/index.php/path/info?query=string', $request->getUri(), '->getUri() with default port without HOST_HEADER');

        // Request with URL REWRITING (hide index.php)
        //   RewriteCond %{REQUEST_FILENAME} !-f
        //   RewriteRule ^(.*)$ index.php [QSA,L]
        // http://host:8080/path/info?query=string
        $server = array();
        $server['HTTP_HOST'] = 'host:8080';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '8080';

        $server['REDIRECT_QUERY_STRING'] = 'query=string';
        $server['REDIRECT_URL'] = '/path/info';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['QUERY_STRING'] = 'query=string';
        $server['REQUEST_URI'] = '/path/info?toto=test&1=1';
        $server['SCRIPT_NAME'] = '/index.php';
        $server['PHP_SELF'] = '/index.php';
        $server['SCRIPT_FILENAME'] = '/some/where/index.php';

        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://host:8080/path/info?query=string', $request->getUri(), '->getUri() with rewrite');

        // Use std port number
        //  http://host/path/info?query=string
        $server['HTTP_HOST'] = 'host';
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('http://host/path/info?query=string', $request->getUri(), '->getUri() with rewrite and default port');

        // Without HOST HEADER
        unset($server['HTTP_HOST']);
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '80';

        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('http://servername/path/info?query=string', $request->getUri(), '->getUri() with rewrite, default port without HOST_HEADER');

        // With encoded characters

        $server = array(
            'HTTP_HOST' => 'host:8080',
            'SERVER_NAME' => 'servername',
            'SERVER_PORT' => '8080',
            'QUERY_STRING' => 'query=string',
            'REQUEST_URI' => '/ba%20se/index_dev.php/foo%20bar/in+fo?query=string',
            'SCRIPT_NAME' => '/ba se/index_dev.php',
            'PATH_TRANSLATED' => 'redirect:/index.php/foo bar/in+fo',
            'PHP_SELF' => '/ba se/index_dev.php/path/info',
            'SCRIPT_FILENAME' => '/some/where/ba se/index_dev.php',
        );

        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals(
            'http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string',
            $request->getUri()
        );

        // with user info

        $server['PHP_AUTH_USER'] = 'fabien';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string', $request->getUri());

        $server['PHP_AUTH_PW'] = 'symfony';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://host:8080/ba%20se/index_dev.php/foo%20bar/in+fo?query=string', $request->getUri());
    }


    public function testGetUserInfo()
    {
        $request = new Request();

        $server = array('PHP_AUTH_USER' => 'fabien');
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('fabien', $request->getUserInfo());

        $server['PHP_AUTH_USER'] = '0';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('0', $request->getUserInfo());

        $server['PHP_AUTH_PW'] = '0';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('0:0', $request->getUserInfo());
    }

    public function testGetSchemeAndHttpHost()
    {
        $request = new Request();

        $server = array();
        $server['SERVER_NAME'] = 'servername';
        $server['SERVER_PORT'] = '90';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://servername:90', $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_USER'] = 'fabien';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://servername:90', $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_USER'] = '0';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://servername:90', $request->getSchemeAndHttpHost());

        $server['PHP_AUTH_PW'] = '0';
        $request->initialize(array(), array(), array(), $server);
        $this->assertEquals('http://servername:90', $request->getSchemeAndHttpHost());
    }

    /**
     * @dataProvider getQueryStringNormalizationData
     */
    public function testGetQueryString($query, $expectedQuery, $msg)
    {
        $request = new Request();

        $request->server->set('QUERY_STRING', $query);
        $this->assertSame($expectedQuery, $request->getQueryString(), $msg);
    }

    public function getQueryStringNormalizationData()
    {
        return array(
            array('foo', 'foo=', 'works with valueless parameters'),
            array('foo=', 'foo=', 'includes a dangling equal sign'),
            array('bar=&foo=bar', 'bar=&foo=bar', '->works with empty parameters'),
            array('foo=bar&bar=', 'bar=&foo=bar', 'sorts keys alphabetically'),

            // GET parameters, that are submitted from a HTML form, encode spaces as "+" by default (as defined in enctype application/x-www-form-urlencoded).
            // PHP also converts "+" to spaces when filling the global _GET or when using the function parse_str.
            array('him=John%20Doe&her=Jane+Doe', 'her=Jane%20Doe&him=John%20Doe', 'normalizes spaces in both encodings "%20" and "+"'),

            array('foo[]=1&foo[]=2', 'foo%5B0%5D=1&foo%5B1%5D=2', 'allows array notation'),
            array('foo=1&foo=2', 'foo=2', 'merges repeated parameters'),
            array('pa%3Dram=foo%26bar%3Dbaz&test=test', 'pa%3Dram=foo%26bar%3Dbaz&test=test', 'works with encoded delimiters'),
            array('0', '0=', 'allows "0"'),
            array('Jane Doe&John%20Doe', 'Jane_Doe=&John_Doe=', 'normalizes encoding in keys'),
            array('her=Jane Doe&him=John%20Doe', 'her=Jane%20Doe&him=John%20Doe', 'normalizes encoding in values'),
            array('foo=bar&&&test&&', 'foo=bar&test=', 'removes unneeded delimiters'),
            array('formula=e=m*c^2', 'formula=e%3Dm%2Ac%5E2', 'correctly treats only the first "=" as delimiter and the next as value'),

            // Ignore pairs with empty key, even if there was a value, e.g. "=value", as such nameless values cannot be retrieved anyway.
            // PHP also does not include them when building _GET.
            array('foo=bar&=a=b&=x=y', 'foo=bar', 'removes params with empty key'),

            // Don't reorder nested query string keys
            array('foo[]=Z&foo[]=A', 'foo%5B0%5D=Z&foo%5B1%5D=A', 'keeps order of values'),
            array('foo[Z]=B&foo[A]=B', 'foo%5BZ%5D=B&foo%5BA%5D=B', 'keeps order of keys'),

            array('utf8=✓', 'utf8=%E2%9C%93', 'encodes UTF-8'),
        );
    }

    public function testGetQueryStringReturnsNull()
    {
        $request = new Request();

        $this->assertNull($request->getQueryString(), '->getQueryString() returns null for non-existent query string');

        $request->server->set('QUERY_STRING', '');
        $this->assertNull($request->getQueryString(), '->getQueryString() returns null for empty query string');
    }

    public function testGetHost()
    {
        $request = new Request();

        $request->initialize(array('foo' => 'bar'));
        $this->assertEquals('', $request->getHost(), '->getHost() return empty string if not initialized');

        $request->initialize(array(), array(), array(), array('HTTP_HOST' => 'www.example.com'));
        $this->assertEquals('www.example.com', $request->getHost(), '->getHost() from Host Header');

        // Host header with port number
        $request->initialize(array(), array(), array(), array('HTTP_HOST' => 'www.example.com:8080'));
        $this->assertEquals('www.example.com', $request->getHost(), '->getHost() from Host Header with port number');

        // Server values
        $request->initialize(array(), array(), array(), array('SERVER_NAME' => 'www.example.com'));
        $this->assertEquals('www.example.com', $request->getHost(), '->getHost() from server name');

        $request->initialize(array(), array(), array(), array('SERVER_NAME' => 'www.example.com', 'HTTP_HOST' => 'www.host.com'));
        $this->assertEquals('www.host.com', $request->getHost(), '->getHost() value from Host header has priority over SERVER_NAME ');
    }

    public function testGetSetMethod()
    {
        $request = new Request();

        $this->assertEquals('GET', $request->getMethod(), '->getMethod() returns GET if no method is defined');

        $request->setMethod('get');
        $this->assertEquals('GET', $request->getMethod(), '->getMethod() returns an uppercased string');

        $request->setMethod('PURGE');
        $this->assertEquals('PURGE', $request->getMethod(), '->getMethod() returns the method even if it is not a standard one');

        $request->setMethod('POST');
        $this->assertEquals('POST', $request->getMethod(), '->getMethod() returns the method POST if no _method is defined');

        $request->setMethod('POST');
        $request->request->set('_method', 'purge');
        $this->assertEquals('POST', $request->getMethod(), '->getMethod() does not return the method from _method if defined and POST but support not enabled');

        $request = new Request();
        $request->setMethod('POST');
        $request->query->set('_method', 'purge');
        $this->assertEquals('POST', $request->getMethod(), '->getMethod() does not return the method from _method if defined and POST but support not enabled');

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('X-HTTP-METHOD-OVERRIDE', 'delete');
        $this->assertEquals('DELETE', $request->getMethod(), '->getMethod() returns the method from X-HTTP-Method-Override even though _method is set if defined and POST');

        $request = new Request();
        $request->setMethod('POST');
        $request->headers->set('X-HTTP-METHOD-OVERRIDE', 'delete');
        $this->assertEquals('DELETE', $request->getMethod(), '->getMethod() returns the method from X-HTTP-Method-Override if defined and POST');

        $request = new Request();
        $request->setMethod('POST');
        $request->query->set('_method', array('delete', 'patch'));
        $this->assertSame('POST', $request->getMethod(), '->getMethod() returns the request method if invalid type is defined in query');
    }

    public function testGetContentWorksTwiceInDefaultMode()
    {
        $req = new Request();
        $this->assertEquals('', $req->getContent());
        $this->assertEquals('', $req->getContent());
    }

    public function testGetContentReturnsResource()
    {
        $req = new Request();
        $retval = $req->getContent(true);
        $this->assertInternalType('resource', $retval);
        $this->assertEquals('', fread($retval, 1));
        $this->assertTrue(feof($retval));
    }

    public function testGetContentReturnsResourceWhenContentSetInConstructor()
    {
        $req = new Request(array(), array(), array(), array(), 'MyContent');
        $resource = $req->getContent(true);

        $this->assertInternalType('resource', $resource);
        $this->assertEquals('MyContent', stream_get_contents($resource));
    }

    public function testContentAsResource()
    {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'My other content');
        rewind($resource);

        $req = new Request(array(), array(), array(), array(), $resource);
        $this->assertEquals('My other content', stream_get_contents($req->getContent(true)));
        $this->assertEquals('My other content', $req->getContent());
    }

    public function getContentCantBeCalledTwiceWithResourcesProvider()
    {
        return array(
            'Resource then fetch' => array(true, false),
            'Resource then resource' => array(true, true),
        );
    }

    /**
     * @dataProvider getContentCanBeCalledTwiceWithResourcesProvider
     */
    public function testGetContentCanBeCalledTwiceWithResources($first, $second)
    {
        $req = new Request();
        $a = $req->getContent($first);
        $b = $req->getContent($second);

        if ($first) {
            $a = stream_get_contents($a);
        }

        if ($second) {
            $b = stream_get_contents($b);
        }

        $this->assertSame($a, $b);
    }

    public function getContentCanBeCalledTwiceWithResourcesProvider()
    {
        return array(
            'Fetch then fetch' => array(false, false),
            'Fetch then resource' => array(false, true),
            'Resource then fetch' => array(true, false),
            'Resource then resource' => array(true, true),
        );
    }

    public function provideOverloadedMethods()
    {
        return array(
            array('PUT'),
            array('DELETE'),
            array('PATCH'),
            array('put'),
            array('delete'),
            array('patch'),
        );
    }


    /**
     * @dataProvider provideOverloadedMethods
     */
    public function testCreateFromGlobals($method)
    {
        $normalizedMethod = strtoupper($method);

        $_GET['foo1'] = 'bar1';
        $_POST['foo2'] = 'bar2';
        $_COOKIE['foo3'] = 'bar3';
        $_FILES['foo4'] = array('bar4');
        $_SERVER['foo5'] = 'bar5';

        $request = Request::createFromGlobals();
        $this->assertEquals('bar1', $request->query->get('foo1'), '::fromGlobals() uses values from $_GET');
        $this->assertEquals('bar2', $request->request->get('foo2'), '::fromGlobals() uses values from $_POST');
        $this->assertEquals('bar5', $request->server->get('foo5'), '::fromGlobals() uses values from $_SERVER');

        unset($_GET['foo1'], $_POST['foo2'], $_COOKIE['foo3'], $_FILES['foo4'], $_SERVER['foo5']);

        Request::createFromGlobals();
        Request::enableHttpMethodParameterOverride();
        $_POST['_method'] = $method;
        $_POST['foo6'] = 'bar6';
        $_SERVER['REQUEST_METHOD'] = 'PoSt';
        $request = Request::createFromGlobals();
        $this->assertEquals($normalizedMethod, $request->getMethod());
        $this->assertEquals('POST', $request->getRealMethod());
        $this->assertEquals('bar6', $request->request->get('foo6'));

        unset($_POST['_method'], $_POST['foo6'], $_SERVER['REQUEST_METHOD']);
        $this->disableHttpMethodParameterOverride();
    }

    private function disableHttpMethodParameterOverride()
    {
        $class = new \ReflectionClass('Zim\\Http\\Request');
        $property = $class->getProperty('httpMethodParameterOverride');
        $property->setAccessible(true);
        $property->setValue(false);
    }

    public function testGetPathInfo()
    {
        $request = new Request();
        $this->assertEquals('/', $request->getPathInfo());

        $server = array();
        $server['REQUEST_URI'] = '/path/info';
        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('/path/info', $request->getPathInfo());

        $server = array();
        $server['REQUEST_URI'] = '/path%20test/info';
        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('/path%20test/info', $request->getPathInfo());

        $server = array();
        $server['REQUEST_URI'] = '?a=b';
        $request->initialize(array(), array(), array(), $server);

        $this->assertEquals('/', $request->getPathInfo());
    }

    public function testGetParameterPrecedence()
    {
        $request = new Request();
        $request->attributes->set('foo', 'attr');
        $request->query->set('foo', 'query');
        $request->request->set('foo', 'body');

        $this->assertSame('attr', $request->get('foo'));

        $request->attributes->remove('foo');
        $this->assertSame('query', $request->get('foo'));

        $request->query->remove('foo');
        $this->assertSame('body', $request->get('foo'));

        $request->request->remove('foo');
        $this->assertNull($request->get('foo'));
    }



    public function testGetRequestFormat()
    {
        $request = new Request();
        $this->assertEquals('html', $request->getRequestFormat());

        // Ensure that setting different default values over time is possible,
        // aka. setRequestFormat determines the state.
        $this->assertEquals('json', $request->getRequestFormat('json'));
        $this->assertEquals('html', $request->getRequestFormat('html'));

        $request = new Request();
        $this->assertNull($request->getRequestFormat(null));

        $request = new Request();
        $this->assertNull($request->setRequestFormat('foo'));
        $this->assertEquals('foo', $request->getRequestFormat(null));

        $request = new Request(array('_format' => 'foo'));
        $this->assertEquals('html', $request->getRequestFormat());
    }

    public function testIsMethod()
    {
        $request = new Request();
        $request->setMethod('POST');
        $this->assertTrue($request->isMethod('POST'));
        $this->assertTrue($request->isMethod('post'));
        $this->assertFalse($request->isMethod('GET'));
        $this->assertFalse($request->isMethod('get'));

        $request->setMethod('GET');
        $this->assertTrue($request->isMethod('GET'));
        $this->assertTrue($request->isMethod('get'));
        $this->assertFalse($request->isMethod('POST'));
        $this->assertFalse($request->isMethod('post'));
    }

    /**
     * @dataProvider getBaseUrlData
     */
    public function testGetBaseUrl($uri, $server, $expectedBaseUrl, $expectedPathInfo)
    {
        $request = Request::create($uri, 'GET', array(), $server);

        $this->assertSame($expectedPathInfo, $request->getPathInfo(), 'pathInfo');
    }

    public function getBaseUrlData()
    {
        return array(
            array(
                '/fruit/strawberry/1234index.php/blah',
                array(
                    'SCRIPT_FILENAME' => 'E:/Sites/cc-new/public_html/fruit/index.php',
                    'SCRIPT_NAME' => '/fruit/index.php',
                    'PHP_SELF' => '/fruit/index.php',
                ),
                '/fruit',
                '/strawberry/1234index.php/blah',
            ),
            array(
                '/fruit/strawberry/1234index.php/blah',
                array(
                    'SCRIPT_FILENAME' => 'E:/Sites/cc-new/public_html/index.php',
                    'SCRIPT_NAME' => '/index.php',
                    'PHP_SELF' => '/index.php',
                ),
                '',
                '/fruit/strawberry/1234index.php/blah',
            ),
            array(
                '/foo%20bar/',
                array(
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ),
                '/foo%20bar',
                '/',
            ),
            array(
                '/foo%20bar/home',
                array(
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ),
                '/foo%20bar',
                '/home',
            ),
            array(
                '/foo%20bar/app.php/home',
                array(
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ),
                '/foo%20bar/app.php',
                '/home',
            ),
            array(
                '/foo%20bar/app.php/home%3Dbaz',
                array(
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo bar/app.php',
                    'SCRIPT_NAME' => '/foo bar/app.php',
                    'PHP_SELF' => '/foo bar/app.php',
                ),
                '/foo%20bar/app.php',
                '/home%3Dbaz',
            ),
            array(
                '/foo/bar+baz',
                array(
                    'SCRIPT_FILENAME' => '/home/John Doe/public_html/foo/app.php',
                    'SCRIPT_NAME' => '/foo/app.php',
                    'PHP_SELF' => '/foo/app.php',
                ),
                '/foo',
                '/bar+baz',
            ),
        );
    }

    /**
     * @dataProvider getLongHostNames
     */
    public function testVeryLongHosts($host)
    {
        $start = microtime(true);

        $request = Request::create('/');
        $request->headers->set('host', $host);
        $this->assertEquals($host, $request->getHost());
        $this->assertLessThan(5, microtime(true) - $start);
    }

    public function getLongHostNames()
    {
        return array(
            array('a'.str_repeat('.a', 40000)),
            array(str_repeat(':', 101)),
        );
    }


    public function nonstandardRequestsData()
    {
        return array(
            array('',  '', '/', 'http://host:8080/', ''),
            array('/', '', '/', 'http://host:8080/', ''),

            array('hello/app.php/x',  '', '/x', 'http://host:8080/hello/app.php/x', '/hello', '/hello/app.php'),
            array('/hello/app.php/x', '', '/x', 'http://host:8080/hello/app.php/x', '/hello', '/hello/app.php'),

            array('',      'a=b', '/', 'http://host:8080/?a=b'),
            array('?a=b',  'a=b', '/', 'http://host:8080/?a=b'),
            array('/?a=b', 'a=b', '/', 'http://host:8080/?a=b'),

            array('x',      'a=b', '/x', 'http://host:8080/x?a=b'),
            array('x?a=b',  'a=b', '/x', 'http://host:8080/x?a=b'),
            array('/x?a=b', 'a=b', '/x', 'http://host:8080/x?a=b'),

            array('hello/x',  '', '/x', 'http://host:8080/hello/x', '/hello'),
            array('/hello/x', '', '/x', 'http://host:8080/hello/x', '/hello'),

            array('hello/app.php/x',      'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'),
            array('hello/app.php/x?a=b',  'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'),
            array('/hello/app.php/x?a=b', 'a=b', '/x', 'http://host:8080/hello/app.php/x?a=b', '/hello', '/hello/app.php'),
        );
    }

    /**
     * @dataProvider nonstandardRequestsData
     */
    public function testNonstandardRequests($requestUri, $queryString, $expectedPathInfo, $expectedUri, $expectedBasePath = '', $expectedBaseUrl = null)
    {
        if (null === $expectedBaseUrl) {
            $expectedBaseUrl = $expectedBasePath;
        }

        $server = array(
            'HTTP_HOST' => 'host:8080',
            'SERVER_PORT' => '8080',
            'QUERY_STRING' => $queryString,
            'PHP_SELF' => '/hello/app.php',
            'SCRIPT_FILENAME' => '/some/path/app.php',
            'REQUEST_URI' => $requestUri,
        );

        $request = new Request(array(), array(), array(), $server);

        $this->assertEquals($expectedPathInfo, $request->getPathInfo());
        $this->assertEquals($expectedUri, $request->getUri());
        $this->assertEquals($queryString, $request->getQueryString());
        $this->assertEquals(8080, $request->getPort());
        $this->assertEquals('host:8080', $request->getHttpHost());
    }
}
