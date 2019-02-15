<?php

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;
use Carbon\Carbon;
use function Jasny\object_set_properties;

/**
 * @covers EventChainRebase
 */
class EventChainRebaseTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    use TestEventTrait;

    /**
     * @var Account&MockObject
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
        $this->rebaser = new EventChainRebase($this->node);

        Carbon::setTestNow('2019-01-01T00:00:00+00:00');
    }

    /**
     * Test 'rebase' method
     */
    public function testRebase()
    {
        $chain = $this->createEventChain(5);
        $fork = $this->createFork($chain, 2, 2);

        $leadChain = $this->createPartialChain($chain, 3);
        $laterChain = $this->createPartialChain($fork, 2);

        $expectedHashes = array_merge(
            Pipeline::with($leadChain->events)->column('hash')->toArray(),
            [base58_encode(hash('sha256', 'forked-event-1', true))],
            [base58_encode(hash('sha256', 'forked-event-2', true))]
        );
        $originalForkHashes = Pipeline::with($laterChain->events)->column('hash')->toArray();

        $unsignedFirstEvent = $this->createLtoEvent([
            'body' => $laterChain->events[0]->body,
            'timestamp' => Carbon::now()->getTimestamp(),
            'previous' => $expectedHashes[2],
            'original' => $this->createLtoEvent($laterChain->events[0]->getValues())
        ]);
        $unsignedSecondEvent = $this->createLtoEvent([
            'body' => $laterChain->events[1]->body,
            'timestamp' => Carbon::now()->getTimestamp(),
            'previous' => $expectedHashes[3],
            'original' => $this->createLtoEvent($laterChain->events[1]->getValues())
        ]);
        $signedFirstEvent = $this->createLtoEvent([
            'signkey' => $this->node->getPublicSignKey(),
            'signature' => 'forked-event-1-signature',
            'hash' => $expectedHashes[3],
        ]);
        $signedSecondEvent = $this->createLtoEvent([
            'signkey' => $this->node->getPublicSignKey(),
            'signature' => 'forked-event-2-signature',
            'hash' => $expectedHashes[4],
        ]);

        $this->node->expects($this->exactly(2))->method('signEvent')
            ->withConsecutive([$unsignedFirstEvent], [$unsignedSecondEvent])
            ->willReturnOnConsecutiveCalls($signedFirstEvent, $signedSecondEvent);

        $result = i\function_call($this->rebaser, $leadChain, $laterChain);
        $this->assertInstanceOf(EventChain::class, $result);

        $events = $result->events;
        $this->assertCount(5, $events);

        $hashes = Pipeline::with($events)->column('hash')->toArray();
        $this->assertEquals($expectedHashes, $hashes);

        $this->assertEquals($chain->events[2], $events[0]);
        $this->assertEquals($chain->events[3], $events[1]);
        $this->assertEquals($chain->events[4], $events[2]);

        $this->assertAttributeEquals($fork->events[2]->body, 'body', $events[3]);
        $this->assertAttributeEquals(Carbon::now()->getTimestamp(), 'timestamp', $events[3]);
        $this->assertAttributeEquals($chain->events[4]->hash, 'previous', $events[3]);
        $this->assertAttributeEquals($this->node->getPublicSignKey(), 'signkey', $events[3]);
        $this->assertAttributeEquals('forked-event-1-signature', 'signature', $events[3]);
        $this->assertAttributeEquals($expectedHashes[3], 'hash', $events[3]);
        $this->assertAttributeEquals($fork->events[2], 'original', $events[3]);
        $this->assertEquals($originalForkHashes[0], $events[3]->original->hash);
        $this->assertEquals($originalForkHashes[0], $events[3]->original->getHash());

        $this->assertAttributeEquals($fork->events[3]->body, 'body', $events[4]);
        $this->assertAttributeEquals(Carbon::now()->getTimestamp(), 'timestamp', $events[4]);
        $this->assertAttributeEquals($expectedHashes[3], 'previous', $events[4]);
        $this->assertAttributeEquals($this->node->getPublicSignKey(), 'signkey', $events[4]);
        $this->assertAttributeEquals('forked-event-2-signature', 'signature', $events[4]);
        $this->assertAttributeEquals($expectedHashes[4], 'hash', $events[4]);
        $this->assertAttributeEquals($fork->events[3], 'original', $events[4]);
        $this->assertEquals($originalForkHashes[1], $events[4]->original->hash);
        $this->assertEquals($originalForkHashes[1], $events[4]->original->getHash());
    }

    /**
     * Create an LTO event.
     *
     * @param array $data
     * @return \LTO\Event
     */
    protected function createLtoEvent(array $data): LTO\Event
    {
        $ltoEvent = new LTO\Event();
        object_set_properties($ltoEvent, $data);

        return $ltoEvent;
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
}
