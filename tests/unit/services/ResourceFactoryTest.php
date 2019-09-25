<?php

/**
 * @covers ResourceFactory
 */
class ResourceFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @var ResourceFactory
     */
    public $manager;
    
    public function _before()
    {
        $this->manager = new ResourceFactory([
            'http://example.com/identity/schema.json#' => Identity::class,
            'http://example.com/external/schema.json#' => ExternalResource::class
        ]);
        
    }
    
    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage DateTime is not a ResourceInterface
     */
    public function testAssertClassFail()
    {
        new ResourceFactory([
            'http://example.com/identity/schema.json#' => Identity::class,
            'http://example.com/external/schema.json#' => DateTime::class
        ]);
    }
    
    /**
     * Factory method, can't properly unit test with PHP 5
     */
    public function testExtractFrom()
    {
        $body = [
            '$schema' => 'http://example.com/external/schema.json#',
            'foo' => 'bar'
        ];

        $event = $this->createMock(Event::class);
        $event->expects($this->atLeastOnce())->method('getBody')->willReturn($body);
        $event->body = json_encode($body);
        
        $resource = $this->manager->extractFrom($event);
        
        $this->assertInstanceOf(ExternalResource::class, $resource);
        $this->assertAttributeEquals('bar', 'foo', $resource);
    }
    
    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid body; no schema for event '3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj'
     */
    public function testExtractFromNoSchema()
    {
        $event = $this->createMock(Event::class);
        $event->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $event->expects($this->atLeastOnce())->method('getBody')->willReturn([
            'foo' => 'bar'
        ]);
        
        $this->manager->extractFrom($event);
    }
    
    public function testExtractFromUnrecognizedSchema()
    {
        $event = $this->createMock(Event::class);
        $event->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $event->expects($this->atLeastOnce())->method('getBody')->willReturn([
            '$schema' => 'http://example.com/foo/schema.json',
            'foo' => 'bar'
        ]);
        
        $resource = $this->manager->extractFrom($event);
         
        $this->assertInstanceOf(ExternalResource::class, $resource);
        $this->assertAttributeEquals('bar', 'foo', $resource);
   }
}
