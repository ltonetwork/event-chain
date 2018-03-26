<?php

/**
 * @covers ResourceStorage
 */
class ResourceStorageTest extends \Codeception\Test\Unit
{
    public $mapping = [
        'lt:/colors/' => 'http://main.example.com/colors/',
        'lt:/foos/' => 'http://foos.example.com/things/',
        'lt:/bars/' => 'http://example.com/bars/'
    ];
    
    public function testGetUrl()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $url = $storage->getURL('lt:/foos/123?v=4ZL83zt5');
        
        $this->assertEquals('http://foos.example.com/things/', $url);
    }
    
    /**
     * @expectedException OutOfRangeException
     */
    public function testGetUrlNotFound()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $storage->getURL('lt:/paws/777');
    }
    
    
    public function testStore()
    {
        $data = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'id' => "lt:/foos/123?v=4ZL83zt5",
            'foo' => 'bar',
            'color' => 'red'
        ];
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn("lt:/foos/123?v=4ZL83zt5");
        $resource->expects($this->once())->method('jsonSerialize')->willReturn($data);
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);

        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $storage->store($resource);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://foos.example.com/things/', (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string)$request->getBody());
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testStoreError()
    {
        $data = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'id' => "lt:/foos/123?v=4ZL83zt5",
            'foo' => 'bar',
            'color' => 'red'
        ];
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn("lt:/foos/123?v=4ZL83zt5");
        $resource->expects($this->once())->method('jsonSerialize')->willReturn($data);
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(500)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $storage->store($resource);
    }
    
    public function testStoreNone()
    {
        $resource = $this->createMock(Comment::class);
        
        $mock = new GuzzleHttp\Handler\MockHandler([]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $storage->store($resource);
    }
}
