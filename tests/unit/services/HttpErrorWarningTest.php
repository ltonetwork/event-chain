<?php

use Psr\Http\Message\ResponseInterface as Response;

/**
 * @covers HttpErrorWarning
 */
class HttpErrorWarningTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;

    /**
     * Test 'notOn' method
     */
    public function testNotOn()
    {
        $service = new HttpErrorWarning();
        $this->assertAttributeEquals([], 'notOn', $service);

        $clone = $service->notOn(1, 2, 403);

        $this->assertNotEquals($service, $clone);
        $this->assertAttributeEquals([], 'notOn', $service);
        $this->assertAttributeEquals([1, 2, 403], 'notOn', $clone);

        $clone2 = $clone->notOn(5, 7, 8, 404);

        $this->assertNotEquals($clone, $clone2);
        $this->assertAttributeEquals([1, 2, 403], 'notOn', $clone);
        $this->assertAttributeEquals([1, 2, 403, 5, 7, 8, 404], 'notOn', $clone2);
    }

    /**
     * Provide data for testing 'invoke' method
     *
     * @return array
     */
    public function invokeProvider()
    {
        return [
            [1, false],
            [2, false],
            [398, false],
            [399, false],
            [400, true, 'Foo error message'],
            [403, true, 'Foo error message'],
            [404, false],
            [500, true],
            [501, false]
        ];
    }

    /**
     * Test '__invoke' method
     *
     * @dataProvider invokeProvider
     */
    public function testInvoke($code, $triggered, $expectedMessage = null)
    {
        $service = new HttpErrorWarning();
        $this->setPrivateProperty($service, 'notOn', [404, 501]);

        $response = $this->createMock(Response::class);

        if ($triggered) {
            $response->expects($this->any())->method('getStatusCode')->willReturn($code);
            $response->expects($this->any())->method('getReasonPhrase')->willReturn('Some reason');
            $response->expects($this->any())->method('getHeaderLine')->with('Content-Type')->willReturn('text/plain');

            if (isset($expectedMessage)) {
                $response->expects($this->any())->method('getBody')->willReturn($expectedMessage);
                $expectedMessage = ': ' . $expectedMessage;
            }

            $this->expectException(PHPUnit\Framework\Exception::class);
            $this->expectExceptionMessage("POST http://foo.bar/baz resulted in a `$code Some reason` response$expectedMessage");
        }

        $service($response, 'http://foo.bar/baz');
    }

    /**
     * Provide data for testing 'invokeMessageProvider' method
     *
     * @return array
     */
    public function invokeMessageProvider()
    {
        return [
            ['text/plain', 'Foo error message'],
            ['text/plain;bar', 'Foo error message'],
            ['application/json', 'Foo error message'],
            ['application/json;bar', 'Foo error message'],
            ['text/plains', null],
            ['application/jsons', null],
            ['image/jpg', null],
            ['image/jpg, text/plain', null],
            ['image/jpg, application/json', null],
            ['foo', null]
        ];
    }

    /**
     * Test '__invoke' method for error message
     *
     * @dataProvider invokeMessageProvider
     */
    public function testInvokeMessage($contentType, $expectedMessage)
    {
        $service = new HttpErrorWarning();
        $response = $this->createMock(Response::class);

        $response->expects($this->any())->method('getStatusCode')->willReturn(404);
        $response->expects($this->any())->method('getReasonPhrase')->willReturn('Some reason');
        $response->expects($this->any())->method('getHeaderLine')->with('Content-Type')->willReturn($contentType);

        if (isset($expectedMessage)) {
            $response->expects($this->any())->method('getBody')->willReturn($expectedMessage);
            $expectedMessage = ': ' . $expectedMessage;
        }

        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage("POST http://foo.bar/baz resulted in a `404 Some reason` response$expectedMessage");

        $service($response, 'http://foo.bar/baz');
    }
}
