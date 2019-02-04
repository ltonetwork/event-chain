<?php

use Improved as i;
use LTO\Account;
use Jasny\DB\EntitySet;
use EventChainRebase\EventsStitch;

/**
 * @covers EventChainRebase
 */
class EventChainRebaseTest extends \Codeception\Test\Unit
{
    /**
     * @var Account
     **/
    protected $node;

    /**
     * @var EventsStitch
     **/
    protected $stitcher;

    /**
     * @var EventChainRebase
     **/
    protected $rebaser;

    /**
     * Do some actions before each test case
     */
    public function _before()
    {
        $this->node = $this->createMock(Account::class);
        $this->stitcher = $this->createMock(EventsStitch::class);
        $this->rebaser = new EventChainRebase($this->node, $this->stitcher);
    }

    /**
     * Provide data for testing 'rebase' method
     *
     * @return array
     */
    public function rebaseProvider()
    {
        return [
            ['z'],
            [null],
        ];
    }

    /**
     * Test 'rebase' method
     *
     * @dataProvider rebaseProvider
     */
    public function testRebase($firstPrevious)
    {
        $chain = $this->createMock(EventChain::class);
        $fork = $this->createMock(EventChain::class);

        list($chainEvents, $forkEvents, $expectedEvents) = $this->getEvents();

        $chainEvents[0]->previous = $firstPrevious;
        $chain->events = $chainEvents;
        $fork->events = $forkEvents;

        $chain->expects($this->once())->method('getFirstEvent')->willReturn($chainEvents[0]);
        $this->stitcher->expects($this->exactly(3))->method('stitch')->withConsecutive(
            [$chainEvents[0], $forkEvents[0], $firstPrevious],
            [$chainEvents[1], $forkEvents[1], 'g'],
            [$chainEvents[2], $forkEvents[2], 'h']
        )->willReturnOnConsecutiveCalls($expectedEvents[0], $expectedEvents[1], $expectedEvents[2]);

        $result = i\function_call($this->rebaser, $chain, $fork);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertEquals($expectedEvents, $result->events->getArrayCopy());
    }

    /**
     * Test 'rebase' method, if chain is shorter then fork
     */
    public function testRebaseChainShorter()
    {
        $chain = $this->createMock(EventChain::class);
        $fork = $this->createMock(EventChain::class);

        list($chainEvents, $forkEvents, $expectedEvents) = $this->getEvents();

        $chain->events = [$chainEvents[0]];
        $fork->events = $forkEvents;

        $chain->expects($this->once())->method('getFirstEvent')->willReturn($chainEvents[0]);
        $this->stitcher->expects($this->exactly(3))->method('stitch')->withConsecutive(
            [$chainEvents[0], $forkEvents[0], 'z'],
            [null, $forkEvents[1], 'g'],
            [null, $forkEvents[2], 'h']
        )->willReturnOnConsecutiveCalls($expectedEvents[0], $expectedEvents[1], $expectedEvents[2]);

        $result = i\function_call($this->rebaser, $chain, $fork);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertEquals($expectedEvents, $result->events->getArrayCopy());
    }

    /**
     * Test 'rebase' method, if fork is shorter then chain
     */
    public function testRebaseForkShorter()
    {
        $chain = $this->createMock(EventChain::class);
        $fork = $this->createMock(EventChain::class);

        list($chainEvents, $forkEvents, $expectedEvents) = $this->getEvents();

        $chain->events = $chainEvents;
        $fork->events = [$forkEvents[0]];

        $chain->expects($this->once())->method('getFirstEvent')->willReturn($chainEvents[0]);
        $this->stitcher->expects($this->exactly(3))->method('stitch')->withConsecutive(
            [$chainEvents[0], $forkEvents[0], 'z'],
            [$chainEvents[1], null, 'g'],
            [$chainEvents[2], null, 'h']
        )->willReturnOnConsecutiveCalls($expectedEvents[0], $expectedEvents[1], $expectedEvents[2]);

        $result = i\function_call($this->rebaser, $chain, $fork);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertEquals($expectedEvents, $result->events->getArrayCopy());
    }

    /**
     * Provide data for testing 'rebase' method, in case when chains are empty
     *
     * @return array
     */
    public function rebaseEmptyProvider()
    {
        return [
            [EntitySet::forClass(Event::class), EntitySet::forClass(Event::class)],
            [[], []]
        ];
    }

    /**
     * Test 'rebase' method, if chains are empty
     *
     * @dataProvider rebaseEmptyProvider
     */
    public function testRebaseEmpty($chainEvents, $forkEvents)
    {
        $chain = $this->createMock(EventChain::class);
        $fork = $this->createMock(EventChain::class);

        $chain->events = $chainEvents;
        $fork->events = $forkEvents;

        $result = $this->rebaser->rebase($chain, $fork);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertSame([], $result->events->getArrayCopy());
    }

    /**
     * Get mock events
     *
     * @return array
     */
    public function getEvents()
    {
        $chain = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $fork = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $expected = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $chain[0]->previous = 'z';
        $chain[0]->hash = 'a';
        $chain[1]->hash = 'b';
        $chain[2]->hash = 'c';

        $fork[0]->hash = 'd';
        $fork[1]->hash = 'e';
        $fork[2]->hash = 'f';

        $expected[0]->hash = 'g';
        $expected[1]->hash = 'h';
        $expected[2]->hash = 'i';

        return [$chain, $fork, $expected];
    }
}
