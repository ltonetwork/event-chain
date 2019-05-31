<?php

/**
 * @covers ExternalResource
 */
class ExternalResourceTest extends \Codeception\Test\Unit
{
    public function testGetId()
    {
        $resource = new ExternalResource();
        $resource->id = 'lt:/foos/123';
        
        $this->assertEquals('lt:/foos/123', $resource->getId());
    }
    
    public function testGetIdProperty()
    {
        $this->assertEquals('id', ExternalResource::getIdProperty());
    }
    
    public function testFromEvent()
    {
        $event = $this->createMock(Event::class);
        $event->body = "77qGgmn5kjj84aS3JRo6bP8mdDr2BSF35dNi5yH3DTZb5Ja2zVa2wo2";
        $event->expects($this->atLeastOnce())->method('getBody')
            ->willReturn(['id' => 'lt:/foos/123', 'color' => 'red']);
        
        $resource = ExternalResource::fromEvent($event);
        
        $this->assertAttributeEquals('lt:/foos/123', 'id', $resource);
        $this->assertAttributeEquals('red', 'color', $resource);
    }

    /**
     * Test 'jsonSerialize' method
     */
    public function testJsonSerialize()
    {
        $resource = new ExternalResource();
        $resource->id = null;
        $resource->timestamp = (new DateTime())->setTimestamp(1234);

        $result = $resource->jsonSerialize();

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertFalse(property_exists($result, 'id'));
        $this->assertSame(1234, $result->timestamp);
    }
}
