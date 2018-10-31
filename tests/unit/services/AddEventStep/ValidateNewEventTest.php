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
 * @covers \AddEventStep\ValidateNewEvent
 */
class ValidateNewEventTest extends \Codeception\Test\Unit
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
        $this->step = new ValidateNewEvent($this->chain);
    }

    public function test()
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $events[2]->hash = 'c';
        $events[3]->hash = 'd';

        $events[0]->previous = 'hh';
        $events[1]->previous = 'hh';
        $events[2]->previous = 'gg';
        $events[3]->previous = 'hh';

        $validations = [
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
            $this->createMock(ValidationResult::class),
        ];

        $events[0]->expects($this->any())->method('validate')->willReturn($validations[0]);
        $events[1]->expects($this->any())->method('validate')->willReturn($validations[1]);
        $events[2]->expects($this->any())->method('validate')->willReturn($validations[2]);
        $events[3]->expects($this->any())->method('validate')->willReturn($validations[3]);

        $addValidation = [
            [$validations[0], "event 'a': "],
            [$validations[1], "event 'b': "],
            [$validations[2], "event 'c': "]
        ];

        $failed = [false, false, false, true];

        $validationResult = $this->createMock(ValidationResult::class);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->exactly(4))->method('failed')
            ->willReturnOnConsecutiveCalls(...$failed);

        $validation->expects($this->exactly(3))->method('add')
            ->willReturnOnConsecutiveCalls(...$addValidation);

        $this->chain->expects($this->exactly(3))->method('getLatestHash')->willReturn('hh');

        $validation->expects($this->once())->method('addError')->with("event '%s' doesn't fit on chain", 'c');
        
        $pipeline = Pipeline::with($events);               
        $ret = i\function_call($this->step, $pipeline, $validation);

        $this->assertSame($pipeline, $ret);
        $this->assertEquals($events, $pipeline->toArray());
    }    
}
