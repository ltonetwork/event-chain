<?php

/**
 * @covers ResourceStorage
 */
class ResourceStorageTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
    public $mapping = [
        'lt:/colors/*' => 'http://main.example.com/colors/',
        'lt:/foos/*' => 'http://foos.example.com/things/',
        'lt:/bars/*' => 'http://example.com/bars/',
        'lt:/bars/*/done' => 'http://example.com/bars/$2/done'
    ];
    
    public function testHasUrlTrue()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $this->assertTrue($storage->hasUrl('lt:/foos/123?v=4ZL83zt5'));
    }

    public function testHasUrlFalse()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $this->assertFalse($storage->hasUrl('lt:/paws/777'));
    }
    
    public function testGetUrl()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $url = $storage->getUrl('lt:/foos/123?v=4ZL83zt5');
        
        $this->assertEquals('http://foos.example.com/things/', $url);
    }
    
    public function testGetUrlParameter()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $url = $storage->getUrl('lt:/bars/333/done');
        
        $this->assertEquals('http://example.com/bars/333/done', $url);
    }
    
    /**
     * @expectedException OutOfRangeException
     */
    public function testGetUrlNotFound()
    {
        $httpClient = $this->createMock(GuzzleHttp\Client::class);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        
        $storage->getUrl('lt:/paws/777');
    }
    
    
    public function storeProvider()
    {
        return [
            ["lt:/foos/123?v=4ZL83zt5", 'http://foos.example.com/things/', []],
            ["lt:/bars/123?v=4ZL83zt5", 'http://example.com/bars/', ['lt:/bars/123/done']]
        ];
    }
    
    /**
     * @dataProvider storeProvider
     * 
     * @param string $id
     * @param string $url
     * @param array  $pending
     */
    public function testStore($id, $url, array $pending)
    {
        $data = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'id' => $id,
            'foo' => 'bar',
            'color' => 'red'
        ];
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn($id);
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
        $this->assertEquals($url, (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string)$request->getBody());
        
        $this->assertAttributeEquals($pending, 'pending', $storage);
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
    
    
    public function testDone()
    {
        $urls = [
            'lt:/bars/123/done' => 'http://example.com/bars/123/done',
            'lt:/bars/890/done' => 'http://example.com/bars/890/done'
        ];
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $storage = new ResourceStorage($this->mapping, $httpClient);
        $this->setPrivateProperty($storage, 'pending', array_keys($urls));
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->atLeastOnce())->method('getId')->willReturn('123');
        $chain->expects($this->atLeastOnce())->method('getLatestHash')->willReturn('abc');
        
        $storage->done($chain);
        
        $this->assertCount(2, $container);
        
        foreach (array_values($urls) as $i => $url) {
            $request = $container[$i]['request'];
            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals($url, (string)$request->getUri());
            $this->assertEquals(['Content-Type' => ['application/json']],
                jasny\array_only($request->getHeaders(), ['Content-Type']));
            $this->assertJsonStringEqualsJsonString(json_encode(['id' => '123', 'lastHash' => 'abc']),
                (string)$request->getBody());
        }
    }
}
