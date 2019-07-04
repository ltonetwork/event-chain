<?php

use LTO\Account;
use Jasny\HttpDigest\HttpDigest;

/**
 * @covers ResourceStorage
 */
class ResourceStorageTest extends \Codeception\Test\Unit
{
    use TestEventTrait;
    use UnitTestHelperTrait;

    /**
     * Test 'store' method
     */
    public function testStore()
    {
        $endpoints = [
            (object)['url' => 'http://www.foo.com', 'schema' => 'http://example.com/foo/schema.json#'],
            (object)['url' => 'http://www.bar.com', 'schema' => 'http://example.com/bar/schema.json#', 'grouped' => 'bar'],
            (object)['url' => 'http://www.zoo.com', 'schema' => 'http://example.com/zoo/schema.json#', 'grouped' => null],
            (object)['url' => 'http://www.zoo-foo.com', 'schema' => 'http://example.com/foo/schema.json#', 'grouped' => null]
        ];

        $chain = $this->getEventChain();
        $resource = $this->getResource();

        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);        

        $node = $this->createMock(Account::class);
        $node->sign = (object)['publickey' => 'foo_node_sign_publickey'];

        $digest = $this->createMock(HttpDigest::class);
        $digest->expects($this->any())->method('create')->with(json_encode($resource))
            ->willReturn('some_calculated_digest');

        $storage = new ResourceStorage($endpoints, $httpClient, $node, $digest);
        $storage->store($resource, $chain);

        $this->assertCount(2, $httpRequestContainer);

        $expectedJson = [
            '$schema' => 'http://example.com/foo/schema.json#',
            'foo' => 'foo_value',
            'bar' => ['id' => 'bar_id'],
            'baz' => ['id' => 'baz_id'],
            'id' => 'foo_external_id',
            'timestamp' => null
        ];

        $urls = ['http://www.foo.com', 'http://www.zoo-foo.com'];
        for ($i=0; $i < count($urls); $i++) { 
            $options = $httpRequestContainer[$i]['options'];
            $request = $httpRequestContainer[$i]['request'];
            $headers = $request->getHeaders();
            $json = json_encode($expectedJson);

            $this->assertTrue($options['http_errors']);
            $this->assertSame(base58_encode('foo_node_sign_publickey'), $options['signature_key_id']);

            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals($urls[$i], (string)$request->getUri());
            $this->assertEquals(['application/json'], $headers['Content-Type']);
            $this->assertEquals(['foo_event_public_signkey'], $headers['X-Original-Key-Id']);
            $this->assertTrue(!empty($headers['date'][0]));
            $this->assertJsonStringEqualsJsonString($json, (string)$request->getBody());            
        }
    }

    /**
     * Test 'store' method with event chain
     */
    public function testStoreEventChain()
    {
        $endpoints = [
            (object)['url' => 'http://www.foo.com/-', 'schema' => 'http://example.com/foo/schema.json#', 'inject_chain' => false],
            (object)['url' => 'http://www.zoo-foo.com/-/zoo', 'schema' => 'http://example.com/foo/schema.json#', 'inject_chain' => 'full'],
            (object)['url' => 'http://www.zoo-foo.com', 'schema' => 'http://example.com/foo/schema.json#', 'inject_chain' => 'empty']
        ];

        $chain = $this->getEventChain();
        $resource = $this->getProcessResource();

        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);        

        $node = $this->createMock(Account::class);
        $node->sign = (object)['publickey' => 'foo_node_sign_publickey'];

        $storage = new ResourceStorage($endpoints, $httpClient, $node);
        $storage->store($resource, $chain);

        $this->assertCount(3, $httpRequestContainer);

        $expected = [
            [
                'url' => 'http://www.foo.com/-',
                'data' => [
                    '$schema' => 'http://example.com/foo/schema.json#',
                    'process' => 'foo_process_id',
                    'scenario' => 'foo_scenario_id',
                    'timestamp' => null
                ]
            ],
            [
                'url' => 'http://www.zoo-foo.com/-/zoo',
                'data' => [
                    '$schema' => 'http://example.com/foo/schema.json#',
                    'process' => 'foo_process_id',
                    'scenario' => 'foo_scenario_id',
                    'timestamp' => null,
                    'chain' => [
                        'id' => $chain->id,
                        'events' => json_decode(json_encode($chain->events)),
                        'identities' => json_decode(json_encode($chain->identities)),
                        'resources' => ['foo', 'bar'],
                        'latestHash' => $chain->getLatestHash()
                    ]
                ]
            ],
            [
                'url' => 'http://www.zoo-foo.com',
                'data' => [
                    '$schema' => 'http://example.com/foo/schema.json#',
                    'process' => 'foo_process_id',
                    'scenario' => 'foo_scenario_id',
                    'timestamp' => null,
                    'chain' => [
                        'id' => $chain->id,
                        'events' => [],
                        'identities' => json_decode(json_encode($chain->identities)),
                        'resources' => ['foo', 'bar'],
                        'latestHash' => $chain->getLatestHash()
                    ]
                ]
            ],
        ];

        for ($i=0; $i < 3; $i++) { 
            $data = $expected[$i];
            $options = $httpRequestContainer[$i]['options'];
            $request = $httpRequestContainer[$i]['request'];
            $headers = $request->getHeaders();

            $this->assertTrue($options['http_errors']);
            $this->assertSame(base58_encode('foo_node_sign_publickey'), $options['signature_key_id']);

            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals($data['url'], (string)$request->getUri());
            $this->assertEquals(['application/json'], $headers['Content-Type']);
            $this->assertEquals(['foo_event_public_signkey'], $headers['X-Original-Key-Id']);
            $this->assertTrue(!empty($headers['date'][0]));
            $body = (string)$request->getBody();
            $this->assertJsonStringEqualsJsonString(json_encode($data['data']), $body);            
        }   
    }

    /**
     * Get test resource
     *
     * @return ExternalResource
     */
    protected function getResource()
    {
        return new class() extends ExternalResource {
            public $schema = 'http://example.com/foo/schema.json#';
            public $id = 'foo_external_id';
            public $foo = 'foo_value';
            public $original_key = 'foo_event_public_signkey';
            public $bar = ['id' => 'bar_id'];
            public $baz = ['id' => 'baz_id'];
            protected $zoo = 'zoo_value';
            private $boom = 'boom_value';

            /**
             * @censored
             */
            public $cenzored_foo = 'skip_this';
        };
    }

    /**
     * Get test resource for process creation
     *
     * @return ExternalResource
     */
    protected function getProcessResource()
    {
        return new class() extends ExternalResource {
            public $schema = 'http://example.com/foo/schema.json#';
            public $process = 'foo_process_id';
            public $scenario = 'foo_scenario_id';
            public $original_key = 'foo_event_public_signkey';
            protected $zoo = 'zoo_value';
            private $boom = 'boom_value';

            /**
             * @censored
             */
            public $cenzored_foo = 'skip_this';
        };
    }

    /**
     * Get test event chain
     *
     * @return EventChain
     */
    protected function getEventChain()
    {
        $chain = $this->createEventChain(3);

        $chain->identities = [
            (new Identity())->setValues(['id' => 'foo']),
            (new Identity())->setValues(['id' => 'bar']),
        ];
        $chain->resources = [
            'foo',
            'bar'
        ];

        return $chain;
    }
}
