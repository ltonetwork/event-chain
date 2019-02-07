<?php

use Improved as i;
use LTO\Account;
use Jasny\DB\EntitySet;

/**
 * @covers EventChainRebase
 */
class EventChainRebaseTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;

    /**
     * @var Account
     **/
    protected $node;

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
        $this->rebaser = $this->createPartialMock(EventChainRebase::class, ['signEvent']);

        $this->setPrivateProperty($this->rebaser, 'node', $this->node);
    }

    /**
     * Test 'rebase' method
     */
    public function testRebase()
    {
        list($leadEvents, $laterEvents) = $this->getEvents();

        $leadChain = (new EventChain())->withEvents($leadEvents);
        $laterChain = (new EventChain())->withEvents($laterEvents);

        $this->rebaser->expects($this->exactly(3))->method('signEvent')->with($this->callback(function($event) {
            return $event instanceof Event && isset($event->timestamp) && isset($event->previous);
        }))->will($this->returnCallback(function($event) {
            $event->hash .= '-signed';
        }));

        $result = i\function_call($this->rebaser, $leadChain, $laterChain);
        $events = $result->events;

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertCount(5, $events);

        $this->assertSame('a', $events[0]->hash);
        $this->assertSame($events[0]->original, $events[2]);
        $this->assertFalse(isset($leadChain->events[0]->original));

        $this->assertSame('b', $events[1]->hash);
        $this->assertSame($events[1]->original, $events[3]);
        $this->assertFalse(isset($leadChain->events[1]->original));

        $this->assertSame('c-signed', $events[2]->hash);
        $this->assertSame('b', $events[2]->previous);
        $this->assertFalse(isset($laterChain->events[0]->previous));
        $this->assertSame($events[2]->original, $events[4]);

        $this->assertSame('d-signed', $events[3]->hash);
        $this->assertSame('c-signed', $events[3]->previous);
        $this->assertFalse(isset($laterChain->events[1]->previous));

        $this->assertSame('e-signed', $events[4]->hash);
        $this->assertSame('d-signed', $events[4]->previous);
        $this->assertFalse(isset($laterChain->events[2]->previous));
    }

    /**
     * Provide data for testing 'rebase' method, if chains are empty
     *
     * @return array
     */
    public function rebaseEmptyProvider()
    {
        return [
            [true, false],
            [false, true],
            [true, true]
        ];
    }

    /**
     * Test 'rebase' method, if chains are empty
     *
     * @dataProvider rebaseEmptyProvider
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Rebasing chains should not be empty
     */
    public function testRebaseEmpty($isLeadEmpty, $isLaterEmpty)
    {
        $leadChain = $this->createMock(EventChain::class);
        $laterChain = $this->createMock(EventChain::class);

        $leadChain->expects($this->any())->method('isEmpty')->willReturn($isLeadEmpty);
        $laterChain->expects($this->any())->method('isEmpty')->willReturn($isLaterEmpty);

        i\function_call($this->rebaser, $leadChain, $laterChain);
    }

    /**
     * Get mock events
     *
     * @return array
     */
    public function getEvents()
    {
        $lead = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $later = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $lead[0]->hash = 'a';
        $lead[1]->hash = 'b';

        $later[0]->hash = 'c';
        $later[1]->hash = 'd';
        $later[2]->hash = 'e';

        return [$lead, $later];
    }

    /**
     * Test 'signEvent' method
     */
    public function testSignEvent()
    {
        $rebaser = new EventChainRebase($this->node);

        $event = $this->createMock(Event::class);
        $event->expects($this->once())->method('signWith')->with($this->node);

        $this->callPrivateMethod($rebaser, 'signEvent', [$event]);
    }
}
