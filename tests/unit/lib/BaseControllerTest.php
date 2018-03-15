<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Jasny\HttpMessage\Uri;

/**
 * @covers BaseController
 */
class BaseControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test 'badRequest' method
     */
    public function testBadRequest()
    {
        $controller = $this->getMockBuilder(BaseController::class)
            ->disableOriginalConstructor()
            ->setMethods(['flash', 'back', 'isXhr'])
            ->getMock();           

        $controller->expects($this->once())->method('isXhr')->willReturn(false);
        $controller->expects($this->once())->method('flash')->with('danger', 'Some message');
        $controller->expects($this->once())->method('back');

        $controller->badRequest('Some message');
    }

    /**
     * Provide data for testing 'getLocalReferer' method
     *
     * @return array
     */
    public function getLocalRefererProvider()
    {
        return [
            ['http://www.localhost.com/some/path', 'www.localhost.com', true],
            ['http://www.test-host.com/some/path', 'www.localhost.com', false]
        ];
    }

    /**
     * Test 'getLocalReferer' method
     *
     * @dataProvider getLocalRefererProvider
     */
    public function testGetLocalReferer($referer, $host, $isLocal)
    {
        list($controller, $request) = $this->getVars();

        $map = [
            ['Referer', $referer],
            ['Host', $host]
        ];

        $controller->expects($this->once())->method('getRequest')->willReturn($request);
        $request->expects($this->exactly(2))->method('getHeaderLine')->with(
            $this->logicalOr('Referer', 'Host')
        )->will($this->returnValueMap($map));

        $result = $controller->getLocalReferer();
        $expected = $isLocal ? $referer : '';

        $this->assertEquals($expected, $result);
    }

    /**
     * Provide data for testing 'isXhr' method
     *
     * @return array
     */
    public function isXhrProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test 'isXhr' method
     *
     * @dataProvider isXhrProvider
     */
    public function testIsXhr($expected)
    {
        list($controller, $request) = $this->getVars();

        $controller->expects($this->once())->method('getRequest')->willReturn($request);
        $request->expects($this->once())->method('getAttribute')->with('is_xhr', false)->willReturn($expected);

        $result = $controller->isXhr();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test 'getSignupUrl' method
     */
    public function testGetSignupUrl()
    {
        $uri = $this->createMock(Uri::class);
        list($controller, $request) = $this->getVars();

        $host = 'http://www.localhost.com';
        $path = '/some/path';
        $hash = 'some_hash';
        $expected = $host . $path . '?c=' . $hash;

        $controller->expects($this->once())->method('getRequest')->willReturn($request);
        $request->expects($this->once())->method('getUri')->willReturn($uri);
        $uri->expects($this->once())->method('withPath')->with($path)->willReturn($uri);
        $uri->expects($this->once())->method('withQuery')->with('c=' . $hash)->willReturn($uri);
        $uri->expects($this->once())->method('withPort')->with('')->willReturn($uri);
        $uri->expects($this->once())->method('__toString')->willReturn($expected);

        $result = $controller->getSignupUrl($path, $hash);

        $this->assertEquals($expected, $result);
    }

    /**
     * Get variables needed for test
     *
     * @return array
     */
    protected function getVars()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->getMockBuilder(BaseController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPersistentSession', 'setResponse', 'getResponse', 'getRequest'])
            ->getMock();           

        return [$controller, $request, $response];
    }
}
