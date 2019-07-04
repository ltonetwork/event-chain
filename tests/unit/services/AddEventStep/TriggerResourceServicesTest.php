<?php declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use Improved as i;
use Event;
use EventChain;
use EventFactory;
use AnchorClient;
use ResourceFactory;
use ResourceTrigger;
use ExternalResource;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \AddEventStep\TriggerResources
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
     * @var ResourceTrigger
     */
    protected $resourceTrigger;

    /**
     * @var Account
     */
    protected $node;

    public function setUp()
    {
        $this->chain = $this->createMock(EventChain::class);
        $this->resourceFactory = $this->createMock(ResourceFactory::class);
        $this->resourceTrigger = $this->createMock(ResourceTrigger::class);
        $this->node = $this->createMock(Account::class);

        $this->step = new TriggerResources($this->chain, $this->resourceFactory, $this->resourceTrigger, $this->node);
    }

    /**
     * Test '__invoke' method
     */
    public function testInvoke()
    {
        $resources = [
            $this->createMock(ExternalResource::class),
            $this->createMock(ExternalResource::class),
            $this->createMock(ExternalResource::class)
        ];

        $addedEvents = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $events[2]->hash = 'c';
        $addedEvents[0]->hash = 'd';
        $addedEvents[1]->hash = 'e';

        $newChain = (new EventChain)->withEvents($events);
        $addedEventsChain = (new EventChain)->withEvents($addedEvents);

        $newEvents = new ArrayObject($events);
        $pipe = Pipeline::with($events);

        $this->resourceFactory->expects($this->exactly(3))->method('extractFrom')
            ->withConsecutive([$events[0]], [$events[1]], [$events[2]])
            ->willReturnOnConsecutiveCalls($resources[0], $resources[1], $resources[2]);

        $this->chain->expects($this->once())->method('withEvents')->with($events)->willReturn($newChain);
        $this->resourceTrigger->expects($this->once())->method('trigger')
            ->with($resources, $this->identicalTo($newChain))->willReturn($addedEventsChain);

        $validation = new ValidationResult();

        $result = i\function_call($this->step, $pipe, $validation, $newEvents);
        $this->assertInstanceOf(Pipeline::class, $result);

        $result->walk();

        $expectedNewEvents = $events;
        $expectedNewEvents[] = $addedEvents[0];
        $expectedNewEvents[] = $addedEvents[1];

        $this->assertEquals($expectedNewEvents, $newEvents->getArrayCopy());
    }

    /**
     * Test '__invoke' method, if validation fails for some event
     */
    public function testInvokeValidationFail()
    {
        $validation = $this->createMock(ValidationResult::class);
        $validation->expects($this->exactly(3))->method('failed')
            ->willReturnOnConsecutiveCalls(false, true, true);

        $resource = $this->createMock(ExternalResource::class);

        $addedEvents = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $events[0]->hash = 'a';
        $events[1]->hash = 'b';
        $events[2]->hash = 'c';
        $addedEvents[0]->hash = 'd';
        $addedEvents[1]->hash = 'e';

        $newEvents = new ArrayObject($events);
        $pipe = Pipeline::with($events);

        $this->resourceFactory->expects($this->once())->method('extractFrom')
            ->with($events[0])->willReturn($resource);

        $this->chain->expects($this->never())->method('withEvents');
        $this->resourceTrigger->expects($this->never())->method('trigger');

        $result = i\function_call($this->step, $pipe, $validation, $newEvents);
        $this->assertInstanceOf(Pipeline::class, $result);

        $result->walk();

        $this->assertEquals($events, $newEvents->getArrayCopy());                
    }
}
