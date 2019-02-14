<?php declare(strict_types=1);

namespace AddEventStep;

use function array_slice;
use Improved as i;
use TestEventTrait;
use Jasny\ValidationResult;
use Improved\IteratorPipeline\Pipeline;

/**
 * @covers \AddEventStep\HandleFork
 */
class HandleForkTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * @var HandleFork
     */
    protected $step;

    public function setUp()
    {        
        $this->conflictResolver = $this->createMock(\ConflictResolver::class);
    }

    /**
     * Test fork case
     */
    public function testFork()
    {
        $chain = $this->createEventChain(5);
        $fork = $this->createFork($chain, 2, 3);
        $merged = $this->createEventChain(6);

        $step = new HandleFork($chain, $this->conflictResolver);

        $chainCallback = function(\EventChain $ourChain) use ($chain) {
            $ourEvents = $ourChain->events;

            $this->assertSame($chain->id, $ourChain->id);
            $this->assertSame(3, $ourEvents->count());

            $this->assertSame($chain->events[2], $ourEvents[0]);
            $this->assertSame($chain->events[3], $ourEvents[1]);
            $this->assertSame($chain->events[4], $ourEvents[2]);

            return true;
        };

        $forkCallback = function(\EventChain $theirChain) use ($fork) {
            $theirEvents = $theirChain->events;

            $this->assertSame($fork->id, $theirChain->id);
            $this->assertSame(3, $theirEvents->count());

            $this->assertSame($fork->events[2], $theirEvents[0]);
            $this->assertSame($fork->events[3], $theirEvents[1]);
            $this->assertSame($fork->events[4], $theirEvents[2]);

            return true;
        };

        $this->conflictResolver->expects($this->once())->method('handleFork')
            ->with($this->callback($chainCallback), $this->callback($forkCallback))->willReturn($merged);

        $pipeline = $this->mapChains($chain, $fork);
        $validation = $this->createMock(ValidationResult::class);

        $result = i\function_call($step, $pipeline, $validation);
        $this->assertInstanceOf(Pipeline::class, $result);

        $events = $result->toArray();
        $this->assertCount(8, $events);

        $expected = array_merge(
            array_slice($chain->events->getArrayCopy(), 0, 2),
            $merged->events->getArrayCopy()
        );

        $this->assertSame($expected, $events);
    }

    public function testNoFork()
    {
        $chain = $this->createEventChain(5);
        $newChain = $this->addEvents($chain, 3);

        $step = new HandleFork($chain, $this->conflictResolver);
        $this->conflictResolver->expects($this->never())->method('handleFork');

        $pipeline = $this->mapChains($chain, $newChain);
        $validation = $this->createMock(ValidationResult::class);

        $result = i\function_call($step, $pipeline, $validation);
        $this->assertInstanceOf(Pipeline::class, $result);

        $events = $result->toArray();
        $this->assertCount(8, $events);

        $this->assertSame($newChain->events->getArrayCopy(), $events);
    }
}
