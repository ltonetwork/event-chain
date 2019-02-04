<?php

namespace EventChainRebase;

use Event;
use Improved as i;
use LTO\Account;
use Jasny\DB\EntitySet;
use function array_without;

/**
 * @covers EventChainRebase\EventStitch
 */
class EventStitchTest extends \Codeception\Test\Unit
{
    use \Jasny\TestHelper;

    /**
     * @var Account
     **/
    protected $node;

    /**
     * @var EventStitch
     **/
    protected $stitcher;

    /**
     * Do some actions before each test case
     */
    public function _before()
    {
        $this->node = $this->createMock(Account::class);
        $this->stitcher = $this->createPartialMock(EventStitch::class, ['createTargetEvent']);

        $this->setPrivateProperty($this->stitcher, 'node', $this->node);
    }

    /**
     * Test '__construct' method
     */
    public function testConstruct()
    {
        $stitcher = new EventStitch($this->node);

        $this->assertAttributeEquals($this->node, 'node', $stitcher);
    }

    /**
     * Provide data for testing 'stitch' method
     *
     * @return array
     */
    public function stitchProvider()
    {
        $chainEventValues = [
            'origin' => 'ch-origin',
            'body' => 'ch-body',
            'timestamp' => 2,
            'previous' => 'ch-previous',
            'signkey' => 'ch-signkey',
            'signature' => 'ch-signature',
            'hash' => 'ch-hash',
            'receipt' => 'ch-receipt',
            'original' => $this->createMock(Event::class)
        ];

        $forkEventValues = [
            'origin' => 'fr-origin',
            'body' => 'fr-body',
            'timestamp' => 2,
            'previous' => 'fr-previous',
            'signkey' => 'fr-signkey',
            'signature' => 'fr-signature',
            'hash' => 'fr-hash',
            'receipt' => 'fr-receipt',
            'original' => $this->createMock(Event::class)
        ];

        $chainEvent = new Event();
        $forkEvent1 = new Event();
        $forkEvent2 = new Event();
        $forkEvent3 = new Event();

        foreach ($chainEventValues as $name => $value) {
            $chainEvent->$name = $value;
        }
        foreach ($forkEventValues as $name => $value) {
            $forkEvent1->$name = $value;
            $forkEvent2->$name = $value;
            $forkEvent3->$name = $value;
        }

        $forkEvent1->timestamp -= 1;
        $forkEvent2->timestamp += 1;

        $previous = 'foo_previous';

        $chainEventValues['previous'] = $previous;
        $forkEventValues['previous'] = $previous;

        $customAssert = ['timestamp', 'original', 'signkey', 'signature', 'hash'];
        $chainEventValues = array_without($chainEventValues, $customAssert);
        $forkEventValues = array_without($forkEventValues, $customAssert);

        $rebaseValues = ['previous' => $previous];

        return [
            [$chainEvent, $forkEvent1, $previous, $forkEvent1, $chainEventValues],
            [$chainEvent, $forkEvent2, $previous, $chainEvent, $forkEventValues],
            [$chainEvent, $forkEvent3, $previous, $forkEvent3, $chainEventValues],
            [null, $forkEvent1, $previous, null, $rebaseValues],
            [$chainEvent, null, $previous, null, $rebaseValues],
        ];
    }

    /**
     * Test 'stitch' method
     *
     * @dataProvider stitchProvider
     */
    public function testStitch(?Event $chainEvent, ?Event $forkEvent, ?string $previous, ?Event $expectedOriginal, array $expectedValues)
    {
        $expected = $this->createMock(Event::class);

        $method = $this->stitcher->expects($this->once())->method('createTargetEvent');
        if (!isset($chainEvent)) {
            $method = $method->with($forkEvent);            
        } elseif (!isset($forkEvent)) {
            $method = $method->with($chainEvent);            
        }
        $method->willReturn($expected);            

        $expected->expects($this->once())->method('signWith')->with($this->node);
        $expected->expects($this->once())->method('setValues')->with($this->callback(function($values) use ($chainEvent, $forkEvent, $expectedOriginal, $expectedValues) {
            $isNewTimestamp = 
                isset($values['timestamp']) && 
                (!isset($chainEvent) || $values['timestamp'] > $chainEvent->timestamp) &&
                (!isset($forkEvent) || $values['timestamp'] > $forkEvent->timestamp);

            $valuesOriginal = $values['original'];
            unset($values['timestamp'], $values['original']);

            return 
                $isNewTimestamp && 
                $expectedOriginal == $valuesOriginal &&
                $expectedValues == $values;
        }));

        $result = i\function_call($this->stitcher, $chainEvent, $forkEvent, $previous);

        $this->assertSame($expected, $result);
    }

    /**
     * Test 'stitch' method, if both events are empty
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Can not stitch two empty events
     */
    public function testStitchEmptyEvents()
    {
        i\function_call($this->stitcher, null, null, 'foo');
    }
}
