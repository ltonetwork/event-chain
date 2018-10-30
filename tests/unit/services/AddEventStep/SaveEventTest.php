<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\SaveEvent
 */
class SaveEventTest extends \Codeception\Test\Unit
{
    /**
     * @var SaveEvent
     */
    protected $step;
    
    /**
     * @var EventChain|MockObject
     */
    protected $chain;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->chain->events = $this->createMock(EntitySet::class);

        $this->step = new SaveEvent($this->chain);
    }

    public function test()
    {             
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = '12345';
        $events[1]->hash = 'abcde';

        $this->chain->events->expects($this->exactly(2))->method('add')->withConsecutive([$events[0]], [$events[1]]);
        $this->chain->expects($this->exactly(2))->method('save');
        
        $pipeline = Pipeline::with($events);
        $ret = i\function_call($this->step, $pipeline);
        $this->assertSame($ret, $pipeline);

        $result = $pipeline->toArray();
        $this->assertEquals($events, $result);
    }
}
