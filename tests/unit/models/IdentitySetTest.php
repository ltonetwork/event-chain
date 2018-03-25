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
            $this->createConfiguredMock(Identity::class, ['getId' => '456'])
        ];
        
        $this->identitySet = new IdentitySet($this->identities);
    }
    
    public function testIdentitySetAdd()
    {
        $identity = $this->createMock(Identity::class);
        $identity->method('getId')->willReturn('789');
        
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
}
