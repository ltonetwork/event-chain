<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use DispatcherManager;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\Dispatch
 */
class DispatchTest extends \Codeception\Test\Unit
{
    /**
     * @var AnchorStep
     */
    protected $step;

    /**
     * @var EventChain|MockObject
     */
    protected $chain;

    /**
     * @var DispatcherManager|MockObject
     */
    protected $dispatcher;

    /**
     * @var Account|MockObject
     */
    protected $node;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->dispatcher = $this->createMock(DispatcherManager::class);
        $this->node = $this->createConfiguredMock(Account::class, ['getPublicSignKey' => '123']);

        $oldNodes = ['amq://example.org', 'amq://example.net'];
        $this->step = new Dispatch($this->chain, $this->dispatcher, $this->node, $oldNodes);
    }


    public function test()
    {
        $partial = $this->createMock(EventChain::class);
        $partial->events = [$this->createMock(Event::class)];

        $onlyEventsChain = $this->createMock(EventChain::class);

        $this->chain->expects($this->once())->method('getNodes')
            ->willReturn(['amq://example.com', 'amq://example.net', 'amq://example.org']);
        $this->chain->expects($this->once())->method('getNodesForSystem')
            ->with(123)
            ->willReturn(['amq://example.net']);
        $this->chain->expects($this->once())->method('onlyWithEvents')->willReturn($onlyEventsChain);

        $this->dispatcher->expects($this->exactly(2))->method('dispatch')
            ->withConsecutive(
                [$this->identicalTo($partial), ['amq://example.org']],
                [$this->identicalTo($onlyEventsChain), ['amq://example.com']]
            );

        i\function_call($this->step, $partial);
    }

    public function testNoNewEvents()
    {
        $partial = $this->createMock(EventChain::class);
        $partial->events = [];

        $onlyEventsChain = $this->createMock(EventChain::class);

        $this->chain->expects($this->once())->method('getNodes')
            ->willReturn(['amq://example.com', 'amq://example.net', 'amq://example.org']);
        $this->chain->expects($this->once())->method('getNodesForSystem')
            ->with(123)
            ->willReturn(['amq://example.net']);
        $this->chain->expects($this->once())->method('onlyWithEvents')->willReturn($onlyEventsChain);

        $this->dispatcher->expects($this->exactly(1))->method('dispatch')
            ->with($this->identicalTo($onlyEventsChain), ['amq://example.com']);

        i\function_call($this->step, $partial);
    }

    public function testNoNewNodes()
    {
        $partial = $this->createMock(EventChain::class);
        $partial->events = [$this->createMock(Event::class)];

        $this->chain->expects($this->once())->method('getNodes')
            ->willReturn(['amq://example.net', 'amq://example.org']);
        $this->chain->expects($this->once())->method('getNodesForSystem')
            ->with(123)
            ->willReturn(['amq://example.net']);

        $this->dispatcher->expects($this->exactly(1))->method('dispatch')
            ->with($this->identicalTo($partial), ['amq://example.org']);

        i\function_call($this->step, $partial);
    }
}
