<?php

use Jasny\DB\EntitySet;

/**
 * @covers TypeCast
 */
class TypeCastTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * Test 'toClass' method, when casting to LTO\Event
     */
    public function testToLToEvent()
    {
        $event = $this->createMock(Event::class);
        $typeCast = $this->createPartialMock(TypeCast::class, ['getValue']);

        $values = [
            'body' => 'foo', 
            'timestamp' => 'bar',
            'previous' => 'baz',
            'signkey' => 'zoo'
        ];

        $typeCast->expects($this->any())->method('getValue')->willReturn($event);
        $event->expects($this->once())->method('getValues')->willReturn($values);

        $result = $typeCast->toClass(LTO\Event::class);

        $this->assertInstanceOf(LTO\Event::class, $result);
        $this->assertSame('foo', $result->body);
        $this->assertSame('bar', $result->timestamp);
        $this->assertSame('baz', $result->previous);
        $this->assertSame('zoo', $result->signkey);
    }

    /**
     * Test 'toClass' method, when casting to EventChain
     */
    public function testToEventChain()
    {
        $event = $this->createMock(Event::class);
        $ltoChain = $this->createMock(LTO\EventChain::class);
        $ltoChain->id = 'foo';
        $ltoChain->events = new EntitySet([$event]);

        $typeCast = $this->createPartialMock(TypeCast::class, ['getValue']);
        $typeCast->expects($this->any())->method('getValue')->willReturn($ltoChain);

        $result = $typeCast->toClass(EventChain::class);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertSame('foo', $result->id);
        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertCount(1, $result->events);
        $this->assertSame($event, $result->events[0]);
    }

    /**
     * Test 'toClass' method, when parent method should be called
     */
    public function testToClassParent()
    {
        $typeCast = new TypeCast(['foo' => 'bar']);
        $result = $typeCast->to('stdClass');

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame('bar', $result->foo);
    }
}
