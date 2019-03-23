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
     * Test 'trigger' method
     */
    public function testTrigger()
    {
        $endpoints = $this->getEndpoints();
        $resources = $this->getResources();

        $httpRequestContainer = [];
        $httpClient = $this->getHttpClientMock($httpRequestContainer, [
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200),
            new GuzzleHttp\Psr7\Response(200)
        ]);        

        $httpError = $this->createMock(HttpErrorWarning::class);
        $httpError->expects($this->never())->method('__invoke');

        $node = $this->createMock(Account::class);
        $node->sign = (object)['publickey' => 'foo_node_sign_publickey'];

        $storage = new ResourceTrigger($endpoints, $httpClient, $httpError, $node);        
        $storage->trigger($resources);

        $this->assertCount(4, $httpRequestContainer);

        $expected = [
            ['url' => 'http://simple-foo-bar.com/foo_value', 'data' => ['foo' => 'foo_value']], // group resources 1 & 2
            ['url' => 'http://simple-foo-bar.com/res3_bar_id', 'data' => ['bar' => 'res3_bar_id']],
            ['url' => 'http://another-foo-bar-zoo.com/path/foo_value/action', 'data' => ['foo' => 'foo_value']], // group resources 1 & 2
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
     * Test 'trigger' method with event chain
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

        return [$resource1, $resource2, $resource3, $resource4];
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
