<?php

use Improved\IteratorPipeline\Pipeline;

/**
 * @covers AnchorClient
 */
class AnchorClientTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    use UnitTestHelperTrait;
    
    public $config = [
        'url' => 'http://anchor.example.com'
    ];

    
    public function testSubmit()
    {
        $hash = 'foo';
        $encoding = 'base58';
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $anchor = new AnchorClient($this->config, $httpClient);
        $anchor->submit($hash, $encoding);
        
        $this->assertCount(1, $container);
        
        $request = $container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals("{$this->config['url']}/hash", (string)$request->getUri());
        $this->assertEquals(['Content-Type' => ['application/json']],
            jasny\array_only($request->getHeaders(), ['Content-Type']));
        $this->assertJsonStringEqualsJsonString(json_encode(compact('hash', 'encoding')), (string)$request->getBody());
    }
    
    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testSubmitError()
    {
        $hash = 'foo';
        $encoding = 'base58';
        
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(500)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);
        
        $anchor = new AnchorClient($this->config, $httpClient);
        $anchor->submit($hash, $encoding);
    }

    /**
     * Test 'fetch' method
     */
    public function testFetch()
    {
        $hash = 'foo';
        $config = ['url' => 'http://some_base_url'];

        $expected = (object)['foo_key' => 'foo_value'];
        $expectedUrl = 'http://some_base_url/hash/foo/encoding/base58';
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetch($hash);       

        $this->assertEquals($expected, $result);
        $this->assertCount(1, $httpRequestContainer);

        $request = $httpRequestContainer[0]['request'];
        $options = $httpRequestContainer[0]['options'];

        $this->assertFalse($options['http_errors']);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals($expectedUrl, (string)$request->getUri());
    }

    /**
     * Test 'fetch' method, if hash is not found
     */
    public function testFetchNotFound()
    {
        $hash = 'foo';
        $config = ['url' => 'http://some_base_url'];

        $expectedUrl = 'http://some_base_url/hash/foo/encoding/base58';
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(404)
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetch($hash);       

        $this->assertEquals(null, $result);
        $this->assertCount(1, $httpRequestContainer);

        $request = $httpRequestContainer[0]['request'];
        $options = $httpRequestContainer[0]['options'];

        $this->assertFalse($options['http_errors']);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals($expectedUrl, (string)$request->getUri());
    }

    /**
     * Provide data for testing 'fetchMultiple' method
     *
     * @return array
     */
    public function fetchMultipleProvider()
    {
        $hashes = ['foo', 'bar', 'zoo', 'baz'];
        $config = ['url' => 'http://some_base_url'];

        $closure = function() use ($hashes): iterable {
            foreach ($hashes as $hash) {
                yield $hash;
            }
        };

        return [
            [$hashes, $config],
            [$closure(), (object)$config]
        ];
    }

    /**
     * Test 'fetchMultiple' method
     *
     * @dataProvider fetchMultipleProvider
     */
    public function testFetchMultiple($hashes, $config)
    {
        $responses = [
            (object)['foo_key' => 'foo_value'],
            (object)['bar_key' => 'bar_value'],
            (object)['baz_key' => 'baz_value']
        ];

        $expected = [
            'foo' => $responses[0],
            'bar' => $responses[1],
            'baz' => $responses[2]
        ];

        $expectedUrls = [
            'http://some_base_url/hash/foo/encoding/base58',
            'http://some_base_url/hash/bar/encoding/base58',
            'http://some_base_url/hash/zoo/encoding/base58',
            'http://some_base_url/hash/baz/encoding/base58'
        ];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
            new GuzzleHttp\Psr7\Response(404),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[2]))
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple($hashes);

        $this->assertInstanceOf(Pipeline::class, $result);

        $array = $result->toArray();        

        $this->assertEquals($expected, $array);
        $this->assertCount(4, $httpRequestContainer);

        foreach ($expectedUrls as $i => $url) {
            $request = $httpRequestContainer[$i]['request'];
            $options = $httpRequestContainer[$i]['options'];

            $this->assertFalse($options['http_errors']);
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals($url, (string)$request->getUri());
        }
    }

    /**
     * Test 'fetchMultiple' method, if encoding is set
     */
    public function testFetchMultipleEncoding()
    {
        $hashes = ['foo', 'bar', 'zoo', 'baz'];
        $config = ['url' => 'http://some_base_url'];

        $responses = [
            (object)['foo_key' => 'foo_value'],
            (object)['bar_key' => 'bar_value'],
            (object)['baz_key' => 'baz_value']
        ];

        $expected = [
            'foo' => $responses[0],
            'bar' => $responses[1],
            'baz' => $responses[2]
        ];

        $expectedUrls = [
            'http://some_base_url/hash/foo/encoding/base64',
            'http://some_base_url/hash/bar/encoding/base64',
            'http://some_base_url/hash/zoo/encoding/base64',
            'http://some_base_url/hash/baz/encoding/base64'
        ];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
            new GuzzleHttp\Psr7\Response(404),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[2]))
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple($hashes, 'base64');

        $this->assertInstanceOf(Pipeline::class, $result);

        $array = $result->toArray();        

        $this->assertEquals($expected, $array);
        $this->assertCount(4, $httpRequestContainer);

        foreach ($expectedUrls as $i => $url) {
            $request = $httpRequestContainer[$i]['request'];
            $options = $httpRequestContainer[$i]['options'];

            $this->assertFalse($options['http_errors']);
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals($url, (string)$request->getUri());
        }
    }

    /**
     * Test 'fetchMultiple' method, if no hashes are provided
     */
    public function testFetchMultipleNoHashes()
    {
        $config = ['url' => 'http://some_base_url'];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, []);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple([]);

        $this->assertInstanceOf(Pipeline::class, $result);

        $array = $result->toArray();        

        $this->assertEquals([], $array);
        $this->assertCount(0, $httpRequestContainer);
    }

    /**
     * Test 'fetchMultiple' method, if neither hash was found
     */
    public function testFetchMultipleNotFound()
    {
        $hashes = ['foo', 'bar', 'zoo', 'baz'];
        $config = ['url' => 'http://some_base_url'];

        $expectedUrls = [
            'http://some_base_url/hash/foo/encoding/base58',
            'http://some_base_url/hash/bar/encoding/base58',
            'http://some_base_url/hash/zoo/encoding/base58',
            'http://some_base_url/hash/baz/encoding/base58'
        ];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(404),
            new GuzzleHttp\Psr7\Response(404),
            new GuzzleHttp\Psr7\Response(404),
            new GuzzleHttp\Psr7\Response(404)
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple($hashes);

        $this->assertInstanceOf(Pipeline::class, $result);

        $array = $result->toArray();        

        $this->assertEquals([], $array);
        $this->assertCount(4, $httpRequestContainer);

        foreach ($expectedUrls as $i => $url) {
            $request = $httpRequestContainer[$i]['request'];
            $options = $httpRequestContainer[$i]['options'];

            $this->assertFalse($options['http_errors']);
            $this->assertEquals('GET', $request->getMethod());
            $this->assertEquals($url, (string)$request->getUri());
        }
    }

    /**
     * Provide data for testing 'fetchMultiple' method, if invalid json is obtained, or it's not an object
     *
     * @return array
     */
    public function fetchMultipleInvalidResponseProvider()
    {
        return [
            [
                "{'invalid': 'json'}", 
                "Failed to decode body as JSON for 'http://some_base_url/hash/zoo/encoding/base58': Syntax error"
            ],
            [
                '"not object"',
                "Expected response for 'http://some_base_url/hash/zoo/encoding/base58' to be an object, got string"
            ]
        ];
    }

    /**
     * Test 'fetchMultiple' method, if invalid json is obtained, or it's not an object
     *
     * @dataProvider fetchMultipleInvalidResponseProvider
     */
    public function testFetchMultipleInvalidResponse($invalidResponse, $exceptionMessage)
    {
        $hashes = ['foo', 'bar', 'zoo', 'baz'];
        $config = ['url' => 'http://some_base_url'];

        $responses = [
            (object)['foo_key' => 'foo_value'],
            (object)['bar_key' => 'bar_value'],
            (object)['baz_key' => 'baz_value']
        ];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
            new GuzzleHttp\Psr7\Response(200, [], $invalidResponse),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[2]))
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple($hashes);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $result->toArray();
    }

    /**
     * Provide data for testing 'fetchMultiple' method, if error code >= 400 was returned
     *
     * @return array
     */
    public function fetchMultipleInvalidResponseCodeProvider()
    {
        return [
            [
                400, 
                'Client error: `GET http://some_base_url/hash/zoo/encoding/base58` resulted in a `400 Bad Request` response'
            ],
            [
                401, 
                'Client error: `GET http://some_base_url/hash/zoo/encoding/base58` resulted in a `401 Unauthorized` response'
            ],
            [
                500, 
                'Server error: `GET http://some_base_url/hash/zoo/encoding/base58` resulted in a `500 Internal Server Error` response'
            ],
        ];
    }

    /**
     * Test 'fetchMultiple' method, if error code >= 400 was returned
     *
     * @dataProvider fetchMultipleInvalidResponseCodeProvider
     */
    public function testFetchMultipleInvalidResponseCode($invalidResponseCode, $exceptionMessage)
    {
        $hashes = ['foo', 'bar', 'zoo', 'baz'];
        $config = ['url' => 'http://some_base_url'];

        $responses = [
            (object)['foo_key' => 'foo_value'],
            (object)['bar_key' => 'bar_value'],
            (object)['baz_key' => 'baz_value']
        ];
        
        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
            new GuzzleHttp\Psr7\Response($invalidResponseCode),
            new GuzzleHttp\Psr7\Response(200, [], json_encode($responses[2]))
        ]);               

        $client = new AnchorClient($config, $httpClient);
        $result = $client->fetchMultiple($hashes);

        $this->expectException(GuzzleHttp\Exception\RequestException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $result->toArray();
    }
}