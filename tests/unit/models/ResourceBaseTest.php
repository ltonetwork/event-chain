<?php

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Jasny\DB\Entity\Dynamic;
use Resource;
use ResourceBase;

/**
 * @covers ResourceBase
 */
class ResourceBaseTest extends \Codeception\Test\Unit
{
    /**
     * @var Resource|MockObject
     */
    public $resource;
    
    public function _before()
    {
        $this->resource = new class() implements Resource, Dynamic {
            use ResourceBase;
        };
    }
    
    public function testFromEvent()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->once())->method('getBody')->willReturn(['foo' => 'bar', 'color' => 'red']);
        
        $class = get_class($this->resource);
        $resource = $class::fromEvent($event);
        
        $this->assertInstanceOf($class, $resource);
        $this->assertAttributeEquals('bar', 'foo', $resource);
        $this->assertAttributeEquals('red', 'color', $resource);
    }
    
    public function setValuesProvider()
    {
        return [
            [ ['schema' => 'http://example.com/schema.json#', 'foo' => 'bar', 'color' => 'red'] ],
            [ ['$schema' => 'http://example.com/schema.json#', 'foo' => 'bar', 'color' => 'red'] ],
            [ (object)['$schema' => 'http://example.com/schema.json#', 'foo' => 'bar', 'color' => 'red'] ]
        ];
    }
    
    /**
     * @dataProvider setValuesProvider
     * 
     * @param array|object $values
     */
    public function testSetValues($values)
    {
        $ret = $this->resource->setValues($values);
        
        $this->assertSame($this->resource, $ret);
        $this->assertAttributeEquals('http://example.com/schema.json#', 'schema', $this->resource);
        $this->assertAttributeEquals('bar', 'foo', $this->resource);
        $this->assertAttributeEquals('red', 'color', $this->resource);
    }
    
    public function testSetIdentity()
    {
        $identity = $this->createMock(Identity::class);
        $ret = $this->resource->setIdentity($identity);
        
        $this->assertSame($this->resource, $ret);
        // Doesn't actually do anything
    }
    
    public function applyPrivilegeProvider()
    {
        return [
            [ ['foo', 'number'], null ],
            [ null, ['color', 'animal'] ],
            [ ['foo', 'number', 'animal'], ['animal'] ],
            [ null, ['schema', 'id', 'identity', 'event', 'timestamp', 'color', 'animal'] ]
        ];
    }
    
    /**
     * @dataProvider applyPrivilegeProvider
     * 
     * @param array|null $only
     * @param array|null $not
     */
    public function testApplyPrivilege($only, $not)
    {
        $identity = $this->createMock(Identity::class);
        $timestamp = new DateTime('2018-03-01T00:00:00+00:00');
        
        $values = [
            '$schema' => 'http://example.com/schema.json#',
            'id' => '123456',
            'identity' => $identity,
            'event' => "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj",
            'timestamp' => $timestamp,
            'foo' => 'bar',
            'color' => 'red',
            'number' => 10,
            'animal' => 'cat'
        ];
        
        $this->resource->setValues($values);
        
        $privilege = $this->createMock(Privilege::class);
        $privilege->only = $only;
        $privilege->not = $not;
        
        $ret = $this->resource->applyPrivilege($privilege);
        $this->assertSame($this->resource, $ret);
        
        $expected = [
            'schema' => 'http://example.com/schema.json#',
            'id' => '123456',
            'identity' => $identity,
            'event' => "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj",
            'timestamp' => $timestamp,
            'foo' => 'bar',
            'number' => 10
        ];
        
        $this->assertEquals($expected, $this->resource->getValues());
    }
}
