<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use TestEventTrait;

/**
 * @covers \AddEventStep\SkipKnownEvents
 */
class SkipKnownEventsTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * @var \EventChain
     */
    protected $chain;

    /**
     * @var SkipKnownEvents
     */
    protected $step;

    public function setUp()
    {
        $this->chain = $this->createEventChain(5);
        $this->step = new SkipKnownEvents($this->chain);
    }

    public function testAdded()
    {
        $newChain = $this->addEvents($this->chain, 2);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->never())->method('addError');
        $validation->expects($this->never())->method('add');

        $newEvents = $this->mapChains($this->chain, $newChain);
        $result = i\function_call($this->step, $newEvents, $validation);

        $this->assertInstanceOf(Pipeline::class, $result);
        $events = i\iterable_to_array($result);

        $expected = Pipeline::with($newChain->events)->slice(5)->values()->toArray();

        $this->assertEquals(
            Pipeline::with($expected)->column('hash')->toArray(),
            Pipeline::with($events)->column('hash')->toArray()
        );
        $this->assertSame($expected, $events);
    }

    public function testAddedPartial()
    {
        $newChain = $this->addEvents($this->chain, 2);
        $partial = $this->createPartialChain($newChain, 3);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->never())->method('addError');
        $validation->expects($this->never())->method('add');

        $newEvents = $this->mapChains($this->createPartialChain($this->chain, 1), $partial);
        $result = i\function_call($this->step, $newEvents, $validation);

        $this->assertInstanceOf(Pipeline::class, $result);
        $events = i\iterable_to_array($result);

        $expected = Pipeline::with($newChain->events)->slice(5)->values()->toArray();

        $this->assertEquals(
            Pipeline::with($expected)->column('hash')->toArray(),
            Pipeline::with($events)->column('hash')->toArray()
        );
        $this->assertSame($expected, $events);
    }

    public function testWithFork()
    {
        $fork = $this->createFork($this->chain, 2, 2);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->never())->method('addError');
        $validation->expects($this->never())->method('add');

        $newEvents = $this->mapChains($this->chain, $fork);
        $result = i\function_call($this->step, $newEvents, $validation);

        $this->assertInstanceOf(Pipeline::class, $result);
        $events = i\iterable_to_array($result);

        $expected = Pipeline::with($fork->events)->slice(2)->values()->toArray();

        $this->assertEquals(
            Pipeline::with($expected)->column('hash')->toArray(),
            Pipeline::with($events)->column('hash')->toArray()
        );
        $this->assertSame($expected, $events);
    }

    public function testWithForkPartial()
    {
        $fork = $this->createFork($this->chain, 2, 2);
        $partial = $this->createPartialChain($fork, 3);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->never())->method('addError');
        $validation->expects($this->never())->method('add');

        $newEvents = $this->mapChains($this->createPartialChain($this->chain, 4), $partial);
        $result = i\function_call($this->step, $newEvents, $validation);

        $this->assertInstanceOf(Pipeline::class, $result);
        $events = i\iterable_to_array($result);

        $expected = Pipeline::with($fork->events)->slice(2)->values()->toArray();

        $this->assertEquals(
            Pipeline::with($expected)->column('hash')->toArray(),
            Pipeline::with($events)->column('hash')->toArray()
        );
        $this->assertSame($expected, $events);
    }
}
