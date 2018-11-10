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

    /**
     * @var ResourceStorage|MockObject
     */
    protected $resourceStorage;

    public function _before()
    {
        $this->anchor = $this->createMock(AnchorClient::class);
        $this->rebaser = $this->createMock(EventChainRebase::class);
        $this->resourceStorage = $this->createMock(ResourceStorage::class);

        $this->resolver = new ConflictResolver($this->anchor, $this->rebaser, $this->resourceStorage);
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
            ->setKeys([$ourEvent, $theirEvent])
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

        $this->resourceStorage->expects($this->once())->method('deleteProjected')
            ->with($mergedChain->resources);

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
            ->setKeys([$ourEvent, $theirEvent])
            ->cleanup();
        $this->anchor->expects($this->once())->method('fetchMultiple')
            ->with($this->callback(function($iterator) use ($ourHash, $theirHash) {
                $this->assertInstanceOf(Iterator::class, $iterator);
                $this->assertEquals([$ourHash, $theirHash], i\iterable_to_array($iterator));
                return true;
            }))
            ->willReturn($anchorResult);

        $this->rebaser->expects($this->never())->method('rebase');
        $this->resourceStorage->expects($this->never())->method('deleteProjected');

        $ret = $this->resolver->handleFork($ourChain, $theirChain);

        $this->assertSame($ret, $emptyChain);
    }
}
