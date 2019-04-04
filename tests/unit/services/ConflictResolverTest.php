<?php

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use PHPUnit\Framework\MockObject\MockObject;

class ConflictResolverTest extends \Codeception\Test\Unit
{
    /**
     * @var ConflictResolver
     */
    protected $resolver;

    /**
     * @var AnchorClient|MockObject
     */
    protected $anchor;

    /**
     * @var EventChainRebase|MockObject
     */
    protected $rebaser;

    public function _before()
    {
        $this->anchor = $this->createMock(AnchorClient::class);
        $this->rebaser = $this->createMock(EventChainRebase::class);

        $this->resolver = new ConflictResolver($this->anchor, $this->rebaser);
    }


    protected function createInfo($block, $position): ?\stdClass
    {
        if ($block === null) {
            return null;
        }

        return (object)[
            'block' => (object)[
                'height' => $block,
            ],
            'transaction' => (object)[
                'position' => $position
            ]
        ];
    }

    protected function createEventChainMock(string $msg)
    {
        $hash = base58_encode(hash('sha256', $msg, true));

        $event = $this->createMock(Event::class);
        $event->hash = $hash;

        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('getFirstEvent')->willReturn($event);

        return [$hash, $event, $chain];
    }

    public function infoProvider()
    {
        return [
            [1000, 3, 1000, 1],
            [1001, 1, 1000, 1],
            [null, null, 1000, 1]
        ];
    }

    /**
     * @dataProvider infoProvider
     */
    public function testTheir($ourBlock, $ourPosition, $theirBlock, $theirPosition)
    {
        [$ourHash, $ourEvent, $ourChain] = $this->createEventChainMock('our');
        [$theirHash, $theirEvent, $theirChain] = $this->createEventChainMock('their');

        $mergedChain = $this->createMock(EventChain::class);
        $mergedChain->resources = [
            $this->createMock(ResourceInterface::class),
            $this->createMock(ResourceInterface::class)
        ];

        $anchorInfo = [
            $this->createInfo($ourBlock, $ourPosition),
            $this->createInfo($theirBlock, $theirPosition)
        ];
        $anchorResult = Pipeline::with($anchorInfo)
            ->setKeys([$ourEvent->hash, $theirEvent->hash])
            ->cleanup();
        $this->anchor->expects($this->once())->method('fetchMultiple')
            ->with($this->callback(function($iterator) use ($ourHash, $theirHash) {
                $this->assertInstanceOf(Iterator::class, $iterator);
                $this->assertEquals([$ourHash, $theirHash], i\iterable_to_array($iterator));
                return true;
            }))
            ->willReturn($anchorResult);

        $this->rebaser->expects($this->once())->method('rebase')
            ->with($theirChain, $ourChain)
            ->willReturn($mergedChain);

        $ret = $this->resolver->handleFork($ourChain, $theirChain);

        $this->assertSame($ret, $mergedChain);
    }

    /**
     * @dataProvider infoProvider
     */
    public function testOur($theirBlock, $theirPosition, $ourBlock, $ourPosition)
    {
        [$ourHash, $ourEvent, $ourChain] = $this->createEventChainMock('our');
        [$theirHash, $theirEvent, $theirChain] = $this->createEventChainMock('their');

        $emptyChain = $this->createMock(EventChain::class);
        $ourChain->expects($this->once())->method('withEvents')->with([])->willReturn($emptyChain);

        $anchorInfo = [
            $this->createInfo($ourBlock, $ourPosition),
            $this->createInfo($theirBlock, $theirPosition)
        ];
        $anchorResult = Pipeline::with($anchorInfo)
            ->setKeys([$ourEvent->hash, $theirEvent->hash])
            ->cleanup();
        $this->anchor->expects($this->once())->method('fetchMultiple')
            ->with($this->callback(function($iterator) use ($ourHash, $theirHash) {
                $this->assertInstanceOf(Iterator::class, $iterator);
                $this->assertEquals([$ourHash, $theirHash], i\iterable_to_array($iterator));
                return true;
            }))
            ->willReturn($anchorResult);

        $this->rebaser->expects($this->never())->method('rebase');

        $ret = $this->resolver->handleFork($ourChain, $theirChain);

        $this->assertSame($ret, $emptyChain);
    }

    /**
     * Test 'handleFork' method, if our last event equals to their last event
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage First event of partial chains should differ, both are 'a'
     */
    public function testHandleForkEqualException()
    {
        $ourEvent = $this->createMock(Event::class);
        $theirEvent = $this->createMock(Event::class);
        $ourEvent->hash = 'a';
        $theirEvent->hash = 'a';

        $ourChain = $this->createMock(EventChain::class);
        $theirChain = $this->createMock(EventChain::class);

        $ourChain->expects($this->once())->method('getFirstEvent')->willReturn($ourEvent);
        $theirChain->expects($this->once())->method('getFirstEvent')->willReturn($theirEvent);

        $this->resolver->handleFork($ourChain, $theirChain);
    }

    /**
     * Provide data for testing 'handleFork' method, if anchor client throws an exception
     *
     * @return array
     */
    public function handleForkAnchorExceptionProvider()
    {
        return [
            [
                RangeException::class, 
                UnresolvableConflictException::class,
                "Events 'a, b' are not anchored yet",
                UnresolvableConflictException::NOT_ANCHORED
            ],
            [
                Exception::class, 
                UnresolvableConflictException::class,
                "Failed to fetch from anchoring service",
                0
            ]
        ];
    }

    /**
     * Test 'handleFork' method, if anchor clietn throws an exception
     *
     * @dataProvider handleForkAnchorExceptionProvider
     */
    public function testHandleForkAnchorException($throwException, $expectedException, $expectedMessage, $expectedCode)
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';

        $ourChain = $this->createMock(EventChain::class);
        $theirChain = $this->createMock(EventChain::class);

        $ourChain->expects($this->once())->method('getFirstEvent')->willReturn($events[0]);
        $theirChain->expects($this->once())->method('getFirstEvent')->willReturn($events[1]);

        $this->anchor->expects($this->once())->method('fetchMultiple')
            ->will($this->returnCallback(function() use ($throwException) {
                throw new $throwException();
            }));                

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);
        $this->expectExceptionCode($expectedCode);            

        $this->resolver->handleFork($ourChain, $theirChain);
    }

    /**
     * Test 'handleFork' method, if events are not anchored
     */
    public function testHandleForkNotAnchored()
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';

        $ourChain = $this->createMock(EventChain::class);
        $theirChain = $this->createMock(EventChain::class);

        $ourChain->expects($this->once())->method('getFirstEvent')->willReturn($events[0]);
        $theirChain->expects($this->once())->method('getFirstEvent')->willReturn($events[1]);

        $this->anchor->expects($this->once())->method('fetchMultiple')
            ->will($this->returnCallback(function() {
                throw new RangeException();
            }));                

        $this->expectException(UnresolvableConflictException::class);
        $this->expectExceptionMessage("Events 'a, b' are not anchored yet");
        $this->expectExceptionCode(UnresolvableConflictException::NOT_ANCHORED);            

        $this->resolver->handleFork($ourChain, $theirChain);
    }
}
