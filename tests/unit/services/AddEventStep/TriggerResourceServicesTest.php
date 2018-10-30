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
 * @covers \AddEventStep\TriggerResourceServices
 */
class TriggerResourceServicesTest extends \Codeception\Test\Unit
{
    /**
     * @var SyncChains
     */
    protected $step;

    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var ResourceStorage
     */
    protected $resourceStorage;

    /**
     * @var Account
     */
    protected $node;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->resourceStorage = $this->createMock(ResourceStorage::class);
        $this->node = $this->createMock(Account::class);

        $this->step = new TriggerResourceServices($this->chain, $this->resourceStorage, $this->node);
    }

    public function provider()
    {
        $event = $this->createMock(Event::class);

        return [
            [true, [], false, false, null],
            [false, [$event], false, false, $event],
            [false, [], true, false, null],
            [true, [], true, false, null],
            [true, [$event], true, true, $event],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function test(bool $validationSuccess, array $partialEvents, bool $isEventSigned, bool $done, ?Event $lastEvent)
    {
        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->once())->method('succeeded')->willReturn($validationSuccess);

        $partial = $this->createMock(EventChain::class);
        $partial->events = $partialEvents;

        $lastEvent = $this->createMock(Event::class);

        $partial->expects($this->any())->method('getLastEvent')->willReturn($lastEvent);
        $this->chain->expects($this->any())->method('isEventSignedByAccount')->with($lastEvent, $this->node)->willReturn($isEventSigned);

        $done ? 
            $this->resourceStorage->expects($this->once())->method('done')->with($this->chain) :
            $this->resourceStorage->expects($this->never())->method('done');
       
        $result = i\function_call($this->step, $partial, $validation);
        $this->assertSame($partial, $result);
    }
}
