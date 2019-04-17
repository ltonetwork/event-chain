<?php

use LTO\Account;
use Jasny\HttpDigest\HttpDigest;

/**
 * @covers ResourceTrigger
 */
class ResourceTriggerTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * Provide data for testing 'trigger' method
     *
     * @return array
     */
    public function triggerProvider()
    {
        $chain = $this->getEventChain();
        $resources = $this->getResources();

        $callable = function() use ($resources) {
            foreach ($resources as $item) {
                yield $item;
            }
        };

        $validateEvents = function(EventChain $chain, $result) {
            $this->assertInstanceOf(EventChain::class, $result);
            $this->assertSame($chain->id, $result->id);
            $this->assertCount(2, $result->events);
            $this->assertSame('foo', $result->events[0]->hash);
            $this->assertSame('baz', $result->events[1]->hash);
        };

        $validateNoEvents = function(EventChain $chain, $result) {
            $this->assertSame(null, $result);
        };

        $partial1 = json_encode(['id' => $chain->id, 'events' => [['hash' => 'foo'], ['hash' => 'baz']]]);
        $partial2 = json_encode(['id' => $chain->id, 'events' => [['hash' => 'not_used']]]);

        return [
            [$chain, $resources, $validateEvents, $partial1, $partial2],
            [$chain, $callable(), $validateEvents, $partial1, $partial2],
            [$chain, $resources, $validateNoEvents],
            [$chain, $callable(), $validateNoEvents]
        ];
    }

    /**
     * Test 'trigger' method, specifically checking obtaining events and grouping resources
     *
     * @dataProvider triggerProvider
     */
    public function testTriggerResultEvents($chain, $resources, $validateResult, $response1 = '', $response2 = '')
    {
        $endpoints = $this->getEndpoints();        
        $responseHeader = ['Content-Type' => 'application/json'];
        $schema = '';

        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200, $responseHeader, '"not object"'),
            new GuzzleHttp\Psr7\Response(200, [], '{"hash": "bar"}'),
            new GuzzleHttp\Psr7\Response(200, $responseHeader, $response1),
            new GuzzleHttp\Psr7\Response(200, $responseHeader, '{}'),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200, $responseHeader, $response2)
        ]);        

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $node = $this->createMock(Account::class);
        $node->sign = (object)['publickey' => 'foo_node_sign_publickey'];

        $storage = new ResourceTrigger($endpoints, $httpClient, $httpError, $node);        
        $result = $storage->trigger($resources, $chain);

        $validateResult($chain, $result);

        $this->assertCount(6, $httpRequestContainer);

        $expected = [
            ['url' => 'http://simple-foo-bar.com/foo_value', 'data' => ['foo' => 'foo_value']], // group resources 1 & 2
            ['url' => 'http://simple-foo-bar.com/res5_foo', 'data' => ['foo' => 'res5_foo']], // resource 5
            ['url' => 'http://simple-foo-bar.com/res3_bar_id', 'data' => ['bar' => 'res3_bar_id']],
            ['url' => 'http://another-foo-bar-zoo.com/path/foo_value/action', 'data' => ['foo' => 'foo_value']], // group resources 1 & 2
            ['url' => 'http://another-foo-bar-zoo.com/path/res5_foo/action', 'data' => ['foo' => 'res5_foo']], // resource 5
            ['url' => 'http://another-foo-bar-zoo.com/path/res3_bar_id/action', 'data' => ['bar' => 'res3_bar_id']]
        ];

        for ($i=0; $i < count($expected); $i++) { 
            $data = $expected[$i];
            $request = $httpRequestContainer[$i]['request'];
            $options = $httpRequestContainer[$i]['options'];
            $headers = array_only($request->getHeaders(), ['Content-Type']);

            $this->assertTrue($options['http_errors']);
            $this->assertSame(base58_encode('foo_node_sign_publickey'), $options['signature_key_id']);

            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals($data['url'], (string)$request->getUri());
            $this->assertEquals(['Content-Type' => ['application/json']], $headers);
            $this->assertJsonStringEqualsJsonString(json_encode($data['data']), (string)$request->getBody());
        }
    }

    /**
     * Test 'trigger' method, specifically checking injecting event chain
     */
    public function testTriggerEventChain()
    {
        $chain = $this->getEventChain();
        $endpoints = $this->getEndpointsEventChain();
        $resources = [$this->getProcessResource()];

        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);        

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $node = $this->createMock(Account::class);
        $node->sign = (object)['publickey' => 'foo_node_sign_publickey'];

        $storage = new ResourceTrigger($endpoints, $httpClient, $httpError, $node);        
        $storage->trigger($resources, $chain);

        $this->assertCount(3, $httpRequestContainer);

        $expected = [
            [
                'url' => 'http://simple-foo.com', 
                'data' => ['scenario' => 'foo_scenario_id']
            ],
            [
                'url' => 'http://another-foo.com', 
                'data' => [
                    'scenario' => 'foo_scenario_id',
                    'chain' => [
                        'id' => $chain->id,
                        'events' => json_decode(json_encode($chain->events)),
                        'identities' => json_decode(json_encode($chain->identities)),
                        'resources' => ['foo', 'bar']
                    ]
                ]
            ],
            [
                'url' => 'http://more-foo.com', 
                'data' => [
                    'scenario' => 'foo_scenario_id',
                    'chain' => [
                        'id' => $chain->id,
                        'events' => [],
                        'identities' => [],
                        'resources' => [],
                        'latest_hash' => $chain->getLatestHash()
                    ]
                ]
            ],
        ];

        for ($i=0; $i < count($expected); $i++) { 
            $data = $expected[$i];
            $request = $httpRequestContainer[$i]['request'];
            $options = $httpRequestContainer[$i]['options'];
            $headers = array_only($request->getHeaders(), ['Content-Type']);

            $this->assertTrue($options['http_errors']);
            $this->assertSame(base58_encode('foo_node_sign_publickey'), $options['signature_key_id']);

            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals($data['url'], (string)$request->getUri());
            $this->assertEquals(['Content-Type' => ['application/json']], $headers);
            $this->assertJsonStringEqualsJsonString(json_encode($data['data']), (string)$request->getBody());
        }
    }

    /**
     * Get endpoints for testing grouped storing
     *
     * @return array
     */
    protected function getEndpoints()
    {
        return [
            (object)[
                'url' => 'http://simple-foo-bar.com/-', 
                'resources' => [
                    (object)[ // resources 1 and 2
                        'schema' => 'http://example.com/foo/schema.json#',
                        'group' => (object)['process' => 'foo']
                    ],
                    (object)[ // resource 3
                        'schema' => 'http://example.com/bar/schema.json#',
                        'group' => (object)['process' => 'bar']
                    ],
                ]
            ],
            (object)[
                'url' => 'http://another-foo-bar-zoo.com/path/-/action', 
                'resources' => [
                    (object)[ // resources 1 and 2
                        'schema' => 'http://example.com/foo/schema.json#',
                        'group' => (object)['process' => 'foo']
                    ],
                    (object)[ // resource 3
                        'schema' => 'http://example.com/bar/schema.json#',
                        'group' => (object)['process' => 'bar']
                    ],
                    (object)[ // no resources match
                        'schema' => 'http://example.com/zoo/schema.json#',
                        'group' => (object)['process' => 'zoo']
                    ],
                ]
            ]
        ];
    }

    /**
     * Get endpoints for testing grouped storing, for also storing event chain
     *
     * @return array
     */
    protected function getEndpointsEventChain()
    {
        return [
            (object)[
                'url' => 'http://simple-foo.com', 
                'inject_chain' => false,
                'resources' => [
                    (object)[
                        'schema' => 'http://example.com/foo/schema.json#',
                        'group' => (object)['process' => 'scenario']
                    ]
                ]
            ],
            (object)[
                'url' => 'http://another-foo.com', 
                'inject_chain' => 'full',
                'resources' => [
                    (object)[
                        'schema' => 'http://example.com/foo/schema.json#',
                        'group' => (object)['process' => 'scenario']
                    ]
                ]
            ],
            (object)[
                'url' => 'http://more-foo.com', 
                'inject_chain' => 'empty',
                'resources' => [
                    (object)[
                        'schema' => 'http://example.com/foo/schema.json#',
                        'group' => (object)['process' => 'scenario']
                    ]
                ]
            ],
        ];
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
            public $id = 'foo_process_id';
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
     * Get resources for grouped storing
     *
     * @return array
     */
    protected function getResources()
    {
        $tmpl = $this->getResource();

        $resource1 = clone $tmpl;
        $resource1->id = 'res1_id';
        $resource1->bar = (object)$resource1->bar;        
        $resource1->baz = (object)$resource1->baz;        
        $resource1->bar->id = 'res1_bar_id';         

        $resource2 = clone $tmpl;
        $resource2->id = 'res2_id';
        $resource2->bar = (object)$resource2->bar;        
        $resource2->baz = (object)$resource2->baz;        
        $resource2->bar->id = 'res2_bar_id';          

        $resource3 = clone $tmpl;        
        $resource3->schema = 'http://example.com/bar/schema.json#';
        $resource3->id = 'res3_id';
        $resource3->foo = 'res3_foo';
        $resource3->bar = (object)$resource3->bar;        
        $resource3->baz = (object)$resource3->baz;        
        $resource3->bar->id= 'res3_bar_id';          

        $resource4 = clone $tmpl;        
        $resource4->schema = 'http://example.com/boom-boom/schema.json#';
        $resource4->id = 'res4_id';
        $resource4->foo = 'res4_foo';
        $resource4->bar = (object)$resource4->bar;        
        $resource4->baz = (object)$resource4->baz;        
        $resource4->bar->id = 'res4_bar_id';        

        $resource5 = clone $tmpl;
        $resource5->id = 'res5_id';
        $resource5->foo = 'res5_foo';
        $resource5->bar = (object)$resource5->bar;        
        $resource5->baz = (object)$resource5->baz;        
        $resource5->bar->id = 'res5_bar_id';         

        return [$resource1, $resource2, $resource3, $resource4, $resource5];
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

    /**
     * Get mock for http client
     *
     * @param array $container
     * @param array $responses 
     * @return GuzzleHttp\Client
     */
    protected function getHttpClientMock(array &$container, array $responses)
    {
        $mock = new GuzzleHttp\Handler\MockHandler($responses);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);

        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);

        return $httpClient;
    }
}
