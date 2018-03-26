<?php

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers IdentitySet
 */
class IdentitySetTest extends \Codeception\Test\Unit
{
    /**
     * @var Identity[]|MockObject[]
     */
    public $identities;
    
    /**
     * @var IdentitySet
     */
    public $identitySet;

    public function _before()
    {
        $this->identities = [
            $this->createConfiguredMock(Identity::class, ['getId' => '123']),
            $this->createConfiguredMock(Identity::class, ['getId' => '456']),
            $this->createConfiguredMock(Identity::class, ['getId' => '789'])
        ];
        
        $this->identitySet = new IdentitySet($this->identities);
    }
    
    public function testIdentitySetAdd()
    {
        $identity = $this->createMock(Identity::class);
        $identity->method('getId')->willReturn('abc');
        
        $this->identitySet->set($identity);
        
        $this->assertSame(array_merge($this->identities, [$identity]), $this->identitySet->getArrayCopy());
    }
    
    public function testIdentitySetUpdate()
    {
        $identity = $this->createMock(Identity::class);
        $identity->method('getId')->willReturn('123');
        $identity->expects($this->once())->method('getValues')->willReturn(['foo' => 'bar']);
        
        $this->identities[0]->expects($this->once())->method('setValues')->with(['foo' => 'bar'])->willReturnSelf();
        
        $this->identitySet->set($identity);
        
        $this->assertSame($this->identities, $this->identitySet->getArrayCopy());
    }

    
    public function filterOnSignkeyProvider()
    {
        return [
            [['123'], 'aaa'],
            [['456'], 'bbb'],
            [['123', '789'], '111'],
            [[], '999']
        ];
    }
    
    /**
     * @dataProvider filterOnSignkeyProvider
     * 
     * @param array  $expected
     * @param string $signkey
     */
    public function testFilterOnSignkey(array $expected, $signkey)
    {
        $this->identities[0]->signkeys = ['user' => 'aaa', 'system' => '111'];
        $this->identities[1]->signkeys = ['user' => 'bbb', 'system' => '222'];
        $this->identities[2]->signkeys = ['user' => 'ccc', 'system' => '111'];
        
        $filteredSet = $this->identitySet->filterOnSignkey($signkey);
        
        $this->assertSame($this->identities, $this->identitySet->getArrayCopy()); // Unchanged
        
        $this->assertInstanceOf(IdentitySet::class, $filteredSet);
        
        $ids = count($filteredSet) > 0 ? $filteredSet->getId() : []; // Workaround issue entityset
        $this->assertSame($expected, $ids);
    }
    
    
    /**
     * 
     * @param string  $schema
     * @param string  $id
     * @param boolean $match
     * @return Privilege|MockObject
     */
    protected function createMockPrivilege($schema, $id, $match)
    {
        $privilege = $this->createMock(Privilege::class);
        $privilege->expects($this->once())->method('match')->with($schema, $id)->willReturn($match);
        
        return $privilege;
    }
    
    public function testGetPrivileges()
    {
        $schema = "http://example.com/foo/schema.json#";
        $id = "lt:/foos/123";
        
        $this->identities[0]->privileges = [
            $this->createMockPrivilege($schema, $id, false),
            $this->createMockPrivilege($schema, $id, true),
            $this->createMockPrivilege($schema, $id, false),
        ];
        $this->identities[1]->privileges = [
            $this->createMockPrivilege($schema, $id, true)
        ];
        $this->identities[2]->privileges = [
            $this->createMockPrivilege($schema, $id, false),
            $this->createMockPrivilege($schema, $id, false)
        ];
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn($id);
        $resource->schema = $schema;
        
        $privileges = $this->identitySet->getPrivileges($resource);
        
        $expected = [
            $this->identities[0]->privileges[1],
            $this->identities[1]->privileges[0]
        ];
        
        $this->assertSame($expected, $privileges);
    }
    
    public function testGetPrivilegesAdmin()
    {
        $schema = "http://example.com/foo/schema.json#";
        $id = "lt:/foos/123";
        
        $this->identities[0]->privileges = [
            $this->createMockPrivilege($schema, $id, false),
            $this->createMockPrivilege($schema, $id, true),
            $this->createMockPrivilege($schema, $id, false),
        ];
        $this->identities[1]->privileges = [
            $this->createMockPrivilege($schema, $id, true)
        ];
        $this->identities[2]->privileges = null; // Admin privs
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn($id);
        $resource->schema = $schema;
        
        $privileges = $this->identitySet->getPrivileges($resource);
        
        $this->assertEquals([ new Privilege() ], $privileges);
    }
    
    public function testGetPrivilegesNone()
    {
        $schema = "http://example.com/foo/schema.json#";
        $id = "lt:/foos/123";
        
        $this->identities[0]->privileges = [
            $this->createMockPrivilege($schema, $id, false),
            $this->createMockPrivilege($schema, $id, false),
        ];
        $this->identities[1]->privileges = [
            $this->createMockPrivilege($schema, $id, false)
        ];
        $this->identities[2]->privileges = [
            $this->createMockPrivilege($schema, $id, false)
        ];
        
        $resource = $this->createMock(ExternalResource::class);
        $resource->method('getId')->willReturn($id);
        $resource->schema = $schema;
        
        $privileges = $this->identitySet->getPrivileges($resource);
        
        $this->assertEquals([], $privileges);
    }
}
