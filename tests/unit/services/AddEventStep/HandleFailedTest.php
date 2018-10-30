<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\HandleFailed
 */
class HandleFailedTest extends \Codeception\Test\Unit
{
    /**
     * @var HandleFailed
     */
    protected $step;

    /**
     * @var EventChain|MockObject
     */
    protected $chain;

    /**
     * @var EventFactory
     */
    protected $eventFactory;


    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->eventFactory = $this->createMock(EventFactory::class);

        $this->step = new HandleFailed($this->chain, $this->eventFactory);
    }

    public function provider()
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class),
        ];

        $events[0]->hash = '12345';
        $events[1]->hash = 'abcde';
        $events[2]->hash = 'foo';
        $events[3]->hash = 'bar';

        $errorEvent = $this->createMock(Event::class);

        return [
            [$events, $errorEvent, [false, false, false, false], '4 errors', $events, [$errorEvent]],
            [$events, $errorEvent, [true, false, true, false], '2 errors', [$events[1], $events[3]], [$events[0], $events[2], $errorEvent]],
            [$events, $errorEvent, [true, true, true, true], '0 errors', [], $events],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function test(array $events, Event $errorEvent, array $success, string $errors, array $failedEvents, array $expectedEvents)
    {             
        $validation = $this->createMock(ValidationResult::class);

        $validation->expects($this->exactly(4))->method('succeeded')->willReturnOnConsecutiveCalls(...$success);

        if ($failedEvents) {
            $validation->expects($this->once())->method('getErrors')->willReturn($errors);
            $this->eventFactory->expects($this->once())->method('createErrorEvent')->with($errors, $failedEvents)->willReturn($errorEvent);
        }
        
        $pipeline = Pipeline::with($events);
        $ret = i\function_call($this->step, $pipeline, $validation);
        $this->assertSame($ret, $pipeline);

        $result = $pipeline->toArray();
        $this->assertEquals($expectedEvents, $result);
    }
}
