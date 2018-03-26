<?php

/**
 * @covers Privilege
 */
class PrivilegeTest extends \Codeception\Test\Unit
{
    public function matchProviderTrue()
    {
        return [
            [ null, null, 'http://example.com/foo/schema.json#', null ],
            [ null, null, 'http://example.com/foo/schema.json#', 'lt:/foos/123' ],
            [ 'http://example.com/foo/schema.json#', null, 'http://example.com/foo/schema.json#', null ],
            [ 'http://example.com/foo/schema.json#', null, 'http://example.com/foo/schema.json#', 'lt:/foos/123' ],
            [ 'http://example.com/foo/schema.json#', 'lt:/foos/123', 'http://example.com/foo/schema.json#', null ],
            [ 'http://example.com/foo/schema.json#', 'lt:/foos/123', 'http://example.com/foo/schema.json#', 'lt:/foos/123' ]
        ];
    }
    
    /**
     * @dataProvider matchProviderTrue
     * 
     * @param string $privSchema
     * @param string $privId
     * @param string $matchSchema
     * @param string $matchId
     */
    public function testMatchTrue($privSchema, $privId, $matchSchema, $matchId)
    {
        $privilege = new Privilege($privSchema, $privId);
        $this->assertTrue($privilege->match($matchSchema, $matchId));
    }
    
    public function matchProviderFalse()
    {
        return [
            [ 'http://example.com/foo/schema.json#', null, 'http://example.com/bar/schema.json#', null ],
            [ 'http://example.com/foo/schema.json#', null, 'http://example.com/bar/schema.json#', 'lt:/foos/123' ],
            [ null, 'lt:/foos/123', 'http://example.com/foo/schema.json#', 'lt:/foos/456' ],
            [ 'http://example.com/foo/schema.json#', 'lt:/foos/123', 'http://example.com/foo/schema.json#', 'lt:/foos/456' ],
        ];
    }
    
    /**
     * @dataProvider matchProviderFalse
     * 
     * @param string $privSchema
     * @param string $privId
     * @param string $matchSchema
     * @param string $matchId
     */
    public function testMatchFalse($privSchema, $privId, $matchSchema, $matchId)
    {
        $privilege = new Privilege($privSchema, $privId);
        $this->assertFalse($privilege->match($matchSchema, $matchId));
    }
    
    
    public function consolidateProvider()
    {
        return [
            [
                [],
                null,
                []
            ],
            [
                null,
                null,
                [
                    Privilege::create()->setValues(['only' => null])
                ]
            ],
            [
                [ 'foo', 'bar' ],
                null,
                [
                    Privilege::create()->setValues(['only' => [ 'foo', 'bar' ]])
                ]
            ],
            [
                null,
                [ 'foo', 'bar' ],
                [
                    Privilege::create()->setValues(['not' => [ 'foo', 'bar' ]])
                ]
            ],
            [
                [ 'foo' ],
                null,
                [
                    Privilege::create()->setValues(['only' => [ 'foo' ], 'not' => [ 'bar' ]])
                ]
            ],
            [
                [ 'foo' ],
                null,
                [
                    Privilege::create()->setValues(['only' => [ 'foo', 'bar' ], 'not' => [ 'bar' ]])
                ]
            ],
            [
                null,
                [ 'bar' ],
                [
                    Privilege::create()->setValues(['only' => [ 'foo' ]]),
                    Privilege::create()->setValues(['not' => [ 'bar' ]])
                ]
            ],
            [
                null,
                [ 'bar' ],
                [
                    Privilege::create()->setValues(['not' => [ 'bar' ]]),
                    Privilege::create()->setValues(['only' => [ 'foo' ]])
                ]
            ],
            [
                null,
                null,
                [
                    Privilege::create()->setValues(['only' => [ 'foo', 'bar' ]]),
                    Privilege::create()->setValues(['not' => [ 'bar' ]])
                ]
            ],
            [
                null,
                null,
                [
                    Privilege::create()->setValues(['not' => [ 'bar' ]]),
                    Privilege::create()->setValues(['only' => [ 'foo', 'bar' ]])
                ]
            ],
            [
                [ 'foo', 'qux', 'bar' ],
                null,
                [
                    Privilege::create()->setValues(['only' => [ 'foo', 'qux' ]]),
                    Privilege::create()->setValues(['only' => [ 'bar' ]])
                ]
            ],
            [
                null,
                null,
                [
                    Privilege::create()->setValues(['not' => [ 'foo' ]]),
                    Privilege::create()->setValues(['not' => [ 'bar' ]])
                ]
            ],
            [
                null,
                [ 'foo' ],
                [
                    Privilege::create()->setValues(['not' => [ 'foo' ]]),
                    Privilege::create()->setValues(['not' => [ 'foo', 'bar' ]])
                ]
            ],
            [
                null,
                [ 'foo' ],
                [
                    Privilege::create()->setValues(['only' => [ 'qux', 'bar' ]]),
                    Privilege::create()->setValues(['not' => [ 'foo', 'bar' ]])
                ]
            ],
        ];
    }
    
    /**
     * @dataProvider consolidateProvider
     * 
     * @param array|null  $only        Expected value for only
     * @param array|null  $not         Expected value for not
     * @param Privilege[] $privileges
     */
    public function testConsolidate($only, $not, $privileges)
    {
        $privilege = new Privilege();
        
        $ret = $privilege->consolidate($privileges);
        
        $this->assertSame($ret, $privilege);
        
        $this->assertAttributeEquals($only, 'only', $privilege);
        $this->assertAttributeEquals($not, 'not', $privilege);
    }
    
    
    public function testCreateFromResource()
    {
        $resource = $this->createMock(Resource::class);
        $resource->schema = "http://example.com/foo/schema.json#";
        
        $privilege = Privilege::create($resource);
        
        $this->assertAttributeEquals("http://example.com/foo/schema.json#", 'schema', $privilege);
        $this->assertAttributeEquals(null, 'id', $privilege);
    }
    
    public function testCreateFromIdentifiableResource()
    {
        $resource = $this->createMock(ExternalResource::class);
        $resource->expects($this->once())->method('getId')->willReturn('lt:/foo/123');
        $resource->schema = "http://example.com/foo/schema.json#";
        
        $privilege = Privilege::create($resource);
        
        $this->assertAttributeEquals("http://example.com/foo/schema.json#", 'schema', $privilege);
        $this->assertAttributeEquals('lt:/foo/123', 'id', $privilege);
    }
}
