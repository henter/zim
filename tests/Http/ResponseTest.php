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
use Zim\Http\Response;

/**
 * @group time-sensitive
 */
class ResponseTest extends TestCase
{
    public function testCreate()
    {
        $response = Response::create('foo', 301, array('Foo' => 'bar'));

        $this->assertInstanceOf('Zim\Http\Response', $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('bar', $response->headers->get('foo'));
    }

    public function testToString()
    {
        $response = new Response();
        $response = explode("\r\n", $response);
        $this->assertEquals('HTTP/1.0 200 OK', $response[0]);
        $this->assertEquals('Cache-Control: no-cache, private', $response[1]);
    }

    public function testClone()
    {
        $response = new Response();
        $responseClone = clone $response;
        $this->assertEquals($response, $responseClone);
    }

    public function testSendHeaders()
    {
        $response = new Response();
        $headers = $response->sendHeaders();
        $this->assertObjectHasAttribute('headers', $headers);
        $this->assertObjectHasAttribute('content', $headers);
        $this->assertObjectHasAttribute('version', $headers);
        $this->assertObjectHasAttribute('statusCode', $headers);
        $this->assertObjectHasAttribute('statusText', $headers);
        $this->assertObjectHasAttribute('charset', $headers);
    }

    public function testSend()
    {
        $response = new Response();
        $responseSend = $response->send();
        $this->assertObjectHasAttribute('headers', $responseSend);
        $this->assertObjectHasAttribute('content', $responseSend);
        $this->assertObjectHasAttribute('version', $responseSend);
        $this->assertObjectHasAttribute('statusCode', $responseSend);
        $this->assertObjectHasAttribute('statusText', $responseSend);
        $this->assertObjectHasAttribute('charset', $responseSend);
    }

    public function testGetCharset()
    {
        $response = new Response();
        $charsetOrigin = 'UTF-8';
        $response->setCharset($charsetOrigin);
        $charset = $response->getCharset();
        $this->assertEquals($charsetOrigin, $charset);
    }

    public function testIsSuccessful()
    {
        $response = new Response();
        $this->assertTrue($response->isSuccessful());
    }

    public function testGetDate()
    {
        $oneHourAgo = $this->createDateTimeOneHourAgo();
        $response = new Response('', 200, array('Date' => $oneHourAgo->format(DATE_RFC2822)));
        $date = $response->getDate();
        $this->assertEquals($oneHourAgo->getTimestamp(), $date->getTimestamp(), '->getDate() returns the Date header if present');

        $response = new Response();
        $date = $response->getDate();
        $this->assertEquals(time(), $date->getTimestamp(), '->getDate() returns the current Date if no Date header present');

        $response = new Response('', 200, array('Date' => $this->createDateTimeOneHourAgo()->format(DATE_RFC2822)));
        $now = $this->createDateTimeNow();
        $response->headers->set('Date', $now->format(DATE_RFC2822));
        $date = $response->getDate();
        $this->assertEquals($now->getTimestamp(), $date->getTimestamp(), '->getDate() returns the date when the header has been modified');

        $response = new Response('', 200);
        $now = $this->createDateTimeNow();
        $response->headers->remove('Date');
        $date = $response->getDate();
        $this->assertEquals($now->getTimestamp(), $date->getTimestamp(), '->getDate() returns the current Date when the header has previously been removed');
    }

    public function testIsPrivate()
    {
        $response = new Response();
        $response->headers->set('Cache-Control', 'max-age=100');
        $response->setPrivate();
        $this->assertEquals(100, $response->headers->getCacheControlDirective('max-age'), '->isPrivate() adds the private Cache-Control directive when set to true');
        $this->assertTrue($response->headers->getCacheControlDirective('private'), '->isPrivate() adds the private Cache-Control directive when set to true');

        $response = new Response();
        $response->headers->set('Cache-Control', 'public, max-age=100');
        $response->setPrivate();
        $this->assertEquals(100, $response->headers->getCacheControlDirective('max-age'), '->isPrivate() adds the private Cache-Control directive when set to true');
        $this->assertTrue($response->headers->getCacheControlDirective('private'), '->isPrivate() adds the private Cache-Control directive when set to true');
        $this->assertFalse($response->headers->hasCacheControlDirective('public'), '->isPrivate() removes the public Cache-Control directive');
    }

    public function testGetSetProtocolVersion()
    {
        $response = new Response();

        $this->assertEquals('1.0', $response->getProtocolVersion());

        $response->setProtocolVersion('1.1');

        $this->assertEquals('1.1', $response->getProtocolVersion());
    }

    public function testDefaultContentType()
    {
        $headerMock = $this->getMockBuilder('Zim\Http\ResponseHeaderBag')->setMethods(array('set'))->getMock();
        $headerMock->expects($this->at(0))
            ->method('set')
            ->with('Content-Type', 'text/html');
        $headerMock->expects($this->at(1))
            ->method('set')
            ->with('Content-Type', 'text/html; charset=UTF-8');

        $response = new Response('foo');
        $response->headers = $headerMock;

        $response->prepare(new Request());
    }

    public function testContentTypeCharset()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/css');

        // force fixContentType() to be called
        $response->prepare(new Request());

        $this->assertEquals('text/css; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function testPrepareDoesNothingIfContentTypeIsSet()
    {
        $response = new Response('foo');
        $response->headers->set('Content-Type', 'text/plain');

        $response->prepare(new Request());

        $this->assertEquals('text/plain; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function testPrepareDoesNothingIfRequestFormatIsNotDefined()
    {
        $response = new Response('foo');

        $response->prepare(new Request());

        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function testPrepareSetContentType()
    {
        $response = new Response('foo');
        $request = Request::create('/');
        $request->setRequestFormat('json');

        $response->prepare($request);

        $this->assertEquals('application/json', $response->headers->get('content-type'));
    }

    public function testPrepareRemovesContentForHeadRequests()
    {
        $response = new Response('foo');
        $request = Request::create('/', 'HEAD');

        $length = 12345;
        $response->headers->set('Content-Length', $length);
        $response->prepare($request);

        $this->assertEquals('', $response->getContent());
        $this->assertEquals($length, $response->headers->get('Content-Length'), 'Content-Length should be as if it was GET; see RFC2616 14.13');
    }

    public function testPrepareRemovesContentForInformationalResponse()
    {
        $response = new Response('foo');
        $request = Request::create('/');

        $response->setContent('content');
        $response->setStatusCode(101);
        $response->prepare($request);
        $this->assertEquals('', $response->getContent());
        $this->assertFalse($response->headers->has('Content-Type'));
        $this->assertFalse($response->headers->has('Content-Type'));

        $response->setContent('content');
        $response->setStatusCode(304);
        $response->prepare($request);
        $this->assertEquals('', $response->getContent());
        $this->assertFalse($response->headers->has('Content-Type'));
        $this->assertFalse($response->headers->has('Content-Length'));
    }

    public function testPrepareRemovesContentLength()
    {
        $response = new Response('foo');
        $request = Request::create('/');

        $response->headers->set('Content-Length', 12345);
        $response->prepare($request);
        $this->assertEquals(12345, $response->headers->get('Content-Length'));

        $response->headers->set('Transfer-Encoding', 'chunked');
        $response->prepare($request);
        $this->assertFalse($response->headers->has('Content-Length'));
    }

    public function testPrepareSetsPragmaOnHttp10Only()
    {
        $request = Request::create('/', 'GET');
        $request->server->set('SERVER_PROTOCOL', 'HTTP/1.0');

        $response = new Response('foo');
        $response->prepare($request);
        $this->assertEquals('no-cache', $response->headers->get('pragma'));
        $this->assertEquals('-1', $response->headers->get('expires'));

        $request->server->set('SERVER_PROTOCOL', 'HTTP/1.1');
        $response = new Response('foo');
        $response->prepare($request);
        $this->assertFalse($response->headers->has('pragma'));
        $this->assertFalse($response->headers->has('expires'));
    }

    public function testSendContent()
    {
        $response = new Response('test response rendering', 200);

        ob_start();
        $response->sendContent();
        $string = ob_get_clean();
        $this->assertContains('test response rendering', $string);
    }

    public function testSetPublic()
    {
        $response = new Response();
        $response->setPublic();

        $this->assertTrue($response->headers->hasCacheControlDirective('public'));
        $this->assertFalse($response->headers->hasCacheControlDirective('private'));
    }

    public function testSetDate()
    {
        $response = new Response();
        $response->setDate(\DateTime::createFromFormat(\DateTime::ATOM, '2013-01-26T09:21:56+0100', new \DateTimeZone('Europe/Berlin')));

        $this->assertEquals('2013-01-26T08:21:56+00:00', $response->getDate()->format(\DateTime::ATOM));
    }

    public function testSetDateWithImmutable()
    {
        $response = new Response();
        $response->setDate(\DateTimeImmutable::createFromFormat(\DateTime::ATOM, '2013-01-26T09:21:56+0100', new \DateTimeZone('Europe/Berlin')));

        $this->assertEquals('2013-01-26T08:21:56+00:00', $response->getDate()->format(\DateTime::ATOM));
    }

    public function testIsInvalid()
    {
        $response = new Response();

        try {
            $response->setStatusCode(99);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue($response->isInvalid());
        }

        try {
            $response->setStatusCode(650);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue($response->isInvalid());
        }

        $response = new Response('', 200);
        $this->assertFalse($response->isInvalid());
    }

    /**
     * @dataProvider getStatusCodeFixtures
     */
    public function testSetStatusCode($code, $text, $expectedText)
    {
        $response = new Response();

        $response->setStatusCode($code, $text);

        $statusText = new \ReflectionProperty($response, 'statusText');
        $statusText->setAccessible(true);

        $this->assertEquals($expectedText, $statusText->getValue($response));
    }

    public function getStatusCodeFixtures()
    {
        return array(
            array('200', null, 'OK'),
            array('200', false, ''),
            array('200', 'foo', 'foo'),
            array('199', null, 'unknown status'),
            array('199', false, ''),
            array('199', 'foo', 'foo'),
        );
    }

    public function testIsInformational()
    {
        $response = new Response('', 100);
        $this->assertTrue($response->isInformational());

        $response = new Response('', 200);
        $this->assertFalse($response->isInformational());
    }

    public function testIsRedirectRedirection()
    {
        foreach (array(301, 302, 303, 307) as $code) {
            $response = new Response('', $code);
            $this->assertTrue($response->isRedirection());
            $this->assertTrue($response->isRedirect());
        }

        $response = new Response('', 304);
        $this->assertTrue($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 200);
        $this->assertFalse($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 404);
        $this->assertFalse($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 301, array('Location' => '/good-uri'));
        $this->assertFalse($response->isRedirect('/bad-uri'));
        $this->assertTrue($response->isRedirect('/good-uri'));
    }

    public function testIsNotFound()
    {
        $response = new Response('', 404);
        $this->assertTrue($response->isNotFound());

        $response = new Response('', 200);
        $this->assertFalse($response->isNotFound());
    }

    public function testIsEmpty()
    {
        foreach (array(204, 304) as $code) {
            $response = new Response('', $code);
            $this->assertTrue($response->isEmpty());
        }

        $response = new Response('', 200);
        $this->assertFalse($response->isEmpty());
    }

    public function testIsForbidden()
    {
        $response = new Response('', 403);
        $this->assertTrue($response->isForbidden());

        $response = new Response('', 200);
        $this->assertFalse($response->isForbidden());
    }

    public function testIsOk()
    {
        $response = new Response('', 200);
        $this->assertTrue($response->isOk());

        $response = new Response('', 404);
        $this->assertFalse($response->isOk());
    }

    public function testIsServerOrClientError()
    {
        $response = new Response('', 404);
        $this->assertTrue($response->isClientError());
        $this->assertFalse($response->isServerError());

        $response = new Response('', 500);
        $this->assertFalse($response->isClientError());
        $this->assertTrue($response->isServerError());
    }

    /**
     * @dataProvider validContentProvider
     */
    public function testSetContent($content)
    {
        $response = new Response();
        $response->setContent($content);
        $this->assertEquals((string) $content, $response->getContent());
    }

    public function testSettersAreChainable()
    {
        $response = new Response();

        $setters = array(
            'setProtocolVersion' => '1.0',
            'setCharset' => 'UTF-8',
            'setPublic' => null,
            'setPrivate' => null,
            'setDate' => $this->createDateTimeNow(),
        );

        foreach ($setters as $setter => $arg) {
            $this->assertEquals($response, $response->{$setter}($arg));
        }
    }

    public function testNoDeprecationsAreTriggered()
    {
        new DefaultResponse();
        $this->getMockBuilder(Response::class)->getMock();

        // we just need to ensure that subclasses of Response can be created without any deprecations
        // being triggered if the subclass does not override any final methods
        $this->addToAssertionCount(1);
    }

    public function validContentProvider()
    {
        return array(
            'obj' => array(new StringableObject()),
            'string' => array('Foo'),
            'int' => array(2),
        );
    }

    public function invalidContentProvider()
    {
        return array(
            'obj' => array(new \stdClass()),
            'array' => array(array()),
            'bool' => array(true, '1'),
        );
    }

    protected function createDateTimeOneHourAgo()
    {
        return $this->createDateTimeNow()->sub(new \DateInterval('PT1H'));
    }

    protected function createDateTimeOneHourLater()
    {
        return $this->createDateTimeNow()->add(new \DateInterval('PT1H'));
    }

    protected function createDateTimeNow()
    {
        $date = new \DateTime();

        return $date->setTimestamp(time());
    }

    protected function createDateTimeImmutableNow()
    {
        $date = new \DateTimeImmutable();

        return $date->setTimestamp(time());
    }

    protected function provideResponse()
    {
        return new Response();
    }
}

class StringableObject
{
    public function __toString()
    {
        return 'Foo';
    }
}

class DefaultResponse extends Response
{
}
