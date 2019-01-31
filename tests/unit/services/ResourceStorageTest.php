<?php

/**
 * @covers ResourceStorage
 */
class ResourceStorageTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
    public function storeProvider()
    {
        return [
            ["lt:/foos/123?v=4ZL83zt5", 'http://foos.example.com/things/'],
            ["lt:/bars/123?v=4ZL83zt5", 'http://example.com/bars/']
        ];
    }
    
    /**
     * @dataProvider storeProvider
     * 
     * @param string $id
     * @param string $url
     */
    public function testStore($id, $url)
    {
        $data = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'id' => $id,
            'foo' => 'bar',
            'color' => 'red'
        ];

        $mapping = $this->createMock(ResourceMapping::class);
        $mapping->expects($this->once())->method('getUrl')->with($id)->willReturn($url);

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

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $storage = new ResourceStorage($mapping, $httpClient, $httpError);
        
        $storage->store($resource);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals($url, (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string)$request->getBody());
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testStoreError()
    {
        $id = "lt:/foos/123?v=4ZL83zt5";

        $data = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'id' => "lt:/foos/123?v=4ZL83zt5",
            'foo' => 'bar',
            'color' => 'red'
        ];

        $mapping = $this->createMock(ResourceMapping::class);
        $mapping->expects($this->once())->method('getUrl')->with($id)
            ->willReturn('http://foos.example.com/things/123');

        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn($id);
        $resource->expects($this->once())->method('jsonSerialize')->willReturn($data);

        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(500)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $storage = new ResourceStorage($mapping, $httpClient, $httpError);
        
        $storage->store($resource);
    }
    
    public function testStoreNone()
    {
        $resource = $this->createMock(Comment::class);
        $mapping = $this->createMock(ResourceMapping::class);
        
        $mock = new GuzzleHttp\Handler\MockHandler([]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $storage = new ResourceStorage($mapping, $httpClient, $httpError);
        
        $storage->store($resource);
    }
    
    
    public function testDone()
    {
        $ids = ['lt:/bars/123/done', 'lt:/bars/890/done'];
        $urls = ['http://example.com/bars/123/done', 'http://example.com/bars/890/done'];

        $resources = [
            $this->createMock(ExternalResource::class),
            $this->createMock(ExternalResource::class)
        ];

        $resources[0]->expects($this->any())->method('getId')->willReturn('lt:/bars/123/done');
        $resources[1]->expects($this->any())->method('getId')->willReturn('lt:/bars/890/done');

        $mapping = $this->createMock(ResourceMapping::class);
        $mapping->expects($this->exactly(2))->method('hasDoneUrl')->withConsecutive([$ids[0]], [$ids[1]])->willReturn(true);
        $mapping->expects($this->exactly(2))->method('getDoneUrl')->withConsecutive([$ids[0]], [$ids[1]])->willReturnOnConsecutiveCalls($urls[0], $urls[1]);

        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->exactly(2))->method('__invoke')->withConsecutive(
            [new GuzzleHttp\Psr7\Response(200), $urls[0]],
            [new GuzzleHttp\Psr7\Response(200), $urls[1]]
        );

        $storage = new ResourceStorage($mapping, $httpClient, $httpError);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->atLeastOnce())->method('getId')->willReturn('123');
        $chain->expects($this->atLeastOnce())->method('getLatestHash')->willReturn('abc');
        
        $storage->done($resources, $chain);
        
        $this->assertCount(2, $container);
        
        foreach ($urls as $i => $url) {
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
