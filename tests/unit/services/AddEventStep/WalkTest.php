<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use ResourceStorage;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\Walk
 */
class WalkTest extends \Codeception\Test\Unit
{
    /**
     * @var SyncChains
     */
    protected $step;

    /**
     * @var EventChain
     */
    protected $chain;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->step = new Walk($this->chain);
    }

    public function test()
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $events[2]->hash = 'c';

        $chainClone = $this->createMock(EventChain::class);

        $this->chain->expects($this->once())->method('withEvents')->with($events)->willReturn($chainClone);
        
        $pipeline = Pipeline::with($events);               
        $ret = i\function_call($this->step, $pipeline);

        $this->assertSame($chainClone, $ret);
    }    
}
