<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use App;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use TestEventTrait;
use ConflictResolver;
use Jasny\DB\EntitySet;
use Jasny\ValidationResult;
use Improved\IteratorPipeline\Pipeline;

/**
 * @covers \AddEventStep\DetermineNewEvents
 */
class DetermineNewEventsTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * @var DetermineNewEvents
     */
    protected $step;

    public function setUp()
    {        
        $this->conflictResolver = $this->createMock(ConflictResolver::class);
    }

    /**
     * Test fork case
     */
    public function testFork()
    {
        $chain = $this->createEventChain(5);
        $fork = $this->createFork($chain, 2, 3);
        $merged = $this->createEventChain(6);

        $step = new DetermineNewEvents($chain, $this->conflictResolver);

        $chainCallback = function(EventChain $ourChain) use ($chain) {
            $ourEvents = $ourChain->events;

            $this->assertSame($chain->id, $ourChain->id);
            $this->assertSame(3, $ourEvents->count());

            $this->assertSame($chain->events[2], $ourEvents[0]);
            $this->assertSame($chain->events[3], $ourEvents[1]);
            $this->assertSame($chain->events[4], $ourEvents[2]);

            return true;
        };

        $forkCallback = function(EventChain $theirChain) use ($fork) {
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
        $events = $result->toArray();

        $this->assertInstanceOf(Pipeline::class, $result);
        $this->assertCount(6, $events);

        for ($i=0; $i < count($events); $i++) { 
            $this->assertSame($merged->events[$i], $events[$i]);
        }
    }
}
