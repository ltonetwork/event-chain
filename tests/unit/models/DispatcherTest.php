<?php

/**
 * @covers Dispatcher
 */
class DispatcherTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
    public $config = [
        'url' => 'http://dispatcher.example.com'
    ];

    
    public function testInfo()
    {
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200, [], json_encode(['node' => 'node1']))
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $result = $dispatcher->info();
        
        $this->assertEquals((object)['node' => 'node1'], $result);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/", (string)$request->getUri());
    }
    
    
    public function testQueue()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(204)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/queue", (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($chain), (string)$request->getBody());
    }
    
    public function testQueueTo()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $to = ['ex1', 'ex2'];
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(204)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain, $to);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/queue?to%5B0%5D=ex1&to%5B1%5D=ex2", (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($chain), (string)$request->getBody());
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testQueueError()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(500)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $dispatcher = new Dispatcher($this->config, $httpClient);
        $dispatcher->queue($chain);
    }
}
