<?php

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler as HttpMockHandler;
use GuzzleHttp\Psr7\Response as HttpResponse;

/**
 * @covers Dispatcher
 */
class DispatcherTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
    public $config = [
        'url' => 'http://dispatcher.example.com'
    ];


    protected function createHttpMock(int $status, ?string $body = null)
    {
        if (isset($body)) {
            $stream = $this->createConfiguredMock(StreamInterface::class, ['__toString' => $body]);
        }

        $mock = new HttpMockHandler([new HttpResponse($status, [], $stream ?? null)]);
        $handler = GuzzleHttp\HandlerStack::create($mock);

        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);

        $httpClient = new HttpClient(['handler' => $handler]);
        $httpClient->container = &$container;

        return $httpClient;
    }
    
    public function testInfo()
    {
        $httpClient = $this->createHttpMock(200, json_encode(['node' => 'node1']));

        $dispatcher = new Dispatcher($this->config, $httpClient);
        $result = $dispatcher->info();
        
        $this->assertEquals((object)['node' => 'node1'], $result);
        
        $this->assertCount(1, $httpClient->container);
        
        $request = $httpClient->container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/", (string)$request->getUri());
    }
    
    
    public function testQueue()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";

        $httpClient = $this->createHttpMock(204);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain);
        
        $this->assertCount(1, $httpClient->container);
        
        $request = $httpClient->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/queue", (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            Jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($chain), (string)$request->getBody());
    }
    
    public function testQueueTo()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $to = ['ex1', 'ex2'];

        $httpClient = $this->createHttpMock(204);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain, $to);
        
        $this->assertCount(1, $httpClient->container);
        
        $request = $httpClient->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/queue?to%5B0%5D=ex1&to%5B1%5D=ex2", (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            Jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($chain), (string)$request->getBody());
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testQueueError()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";

        $httpClient = $this->createHttpMock(500);

        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain);
    }
}
