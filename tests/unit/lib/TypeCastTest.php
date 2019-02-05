<?php

/**
 * @covers TypeCast
 */
class TypeCastTest extends \Codeception\Test\Unit
{
    /**
     * Test 'toClass' method
     */
    public function testToClass()
    {
        $event = $this->createMock(Event::class);
        $typeCast = $this->createPartialMock(TypeCast::class, ['getValue']);

        $values = [
            'body' => 'foo', 
            'timestamp' => 'bar',
            'previous' => 'baz',
            'signkey' => 'zoo'
        ];

        $typeCast->expects($this->exactly(2))->method('getValue')->willReturn($event);
        $event->expects($this->once())->method('getValues')->willReturn($values);

        $result = $typeCast->toClass(LTO\Event::class);

        $this->assertInstanceOf(LTO\Event::class, $result);
        $this->assertSame('foo', $result->body);
        $this->assertSame('bar', $result->timestamp);
        $this->assertSame('baz', $result->previous);
        $this->assertSame('zoo', $result->signkey);
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
