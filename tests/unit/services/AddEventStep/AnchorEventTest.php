<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Event;
use EventChain;
use AnchorClient;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\AnchorEvent
 */
class AnchorEventTest extends \Codeception\Test\Unit
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
     * @var AnchorClient|MockObject
     */
    protected $client;

    /**
     * @var Account|MockObject
     */
    protected $node;


    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->client = $this->createMock(AnchorClient::class);
        $this->node = $this->createMock(Account::class);

        $this->step = new AnchorEvent($this->chain, $this->node, $this->client);
    }

    public function provider()
    {
        return [
            [[false, false], [true, true], 2, 2],
            [[false, true], [true, true], 1, 1],
            [[false, false], [true, false], 2, 1],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function test(array $failed, array $owned, int $callsOwned, int $callsSubmit)
    {
        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
        ];
        $events[0]->hash = '12345';
        $events[1]->hash = 'abcde';

        $pipeline = Pipeline::with($events);

        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->exactly(2))->method('failed')
            ->willReturnOnConsecutiveCalls(...$failed);

        $this->chain->expects($this->exactly($callsOwned))->method('isEventSignedByAccount')
            ->withConsecutive([$events[0], $this->node], [$events[1], $this->node])
            ->willReturnOnConsecutiveCalls(...$owned);

        $this->client->expects($this->exactly($callsSubmit))->method('submit')
            ->withConsecutive([$events[0]->hash], [$events[1]->hash]);

        $ret = i\function_call($this->step, $pipeline, $validation);
        $this->assertSame($ret, $pipeline);

        $result = $pipeline->toArray();
        $this->assertSame($events, $result);
    }
}
