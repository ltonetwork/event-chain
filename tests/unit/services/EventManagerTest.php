<?php

use Jasny\ValidationResult;
use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AddEventStep as Step;

/**
 * @covers \EventManager
 */
class EventManagerTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    use TestEventTrait;

    /**
     * Create mock dependencies for EventManager
     *
     * @return array
     */
    protected function createMockDependencies(): array
    {
        return [
            ResourceFactory::class => $this->createMock(ResourceFactory::class),
            ResourceStorage::class => $this->createMock(ResourceStorage::class),
            ResourceTrigger::class => $this->createMock(ResourceTrigger::class),
            DispatcherManager::class => $this->createMock(DispatcherManager::class),
            EventFactory::class => $this->createMock(EventFactory::class),
            AnchorClient::class => $this->createMock(AnchorClient::class),
            EventChainGateway::class => $this->createMock(EventChainGateway::class),
            ConflictResolver::class => $this->createMock(ConflictResolver::class),
        ];
    }

    /**
     * Test 'add' method
     */
    public function testAdd()
    {
        $chain = $this->createEventChain(2);
        $newChain = $this->addEvents($chain, 2, null, true);

        $steps = $this->mockSteps($chain, $newChain);
        $manager = $this->createEventManager(['getSteps']);

        $manager->expects($this->once())->method('getSteps')->willReturn($steps);

        $result = $manager->add($chain, $newChain);
        $expectedErrors = [
            'first step error',
            'second step error'
        ];

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals($expectedErrors, $result->getErrors());
    }

    /**
     * Create a partial mock EventManager
     * 
     * @param array|null  $methods
     * @param array       $dependencies
     * @return EventManager|MockObject
     */
    protected function createEventManager(?array $methods = null, array $dependencies = [])
    {
        $constructorArgs = array_values(array_merge($this->createMockDependencies(), $dependencies));

        $eventManager = $this->getMockBuilder(EventManager::class)
            ->setConstructorArgs($constructorArgs)
            ->setMethods($methods)
            ->getMock();        

        return $eventManager;
    }

    /**
     * Mock event manager steps
     * 
     * @return array
     */
    protected function mockSteps($chain, $newChain)
    {
        return [
            function(ArrayObject $newEvents, ValidationResult $validation, ArrayObject $newEvents2) use ($newChain) : Pipeline {
                $this->assertEquals($newChain->events->getArrayCopy(), $newEvents->getArrayCopy());                
                $this->assertEquals($newChain->events->getArrayCopy(), $newEvents2->getArrayCopy());

                $validation->addError('first step error');

                return Pipeline::with($newEvents);
            },
            function(Pipeline $pipe, ValidationResult $validation, ArrayObject $newEvents) use ($newChain) {
                $this->assertEquals($newChain->events->getArrayCopy(), $newEvents->getArrayCopy());
                $this->assertEquals($newChain->events->getArrayCopy(), $pipe->toArray());

                $validation->addError('second step error');
            }
        ];
    }

    /**
     * Test 'add' method for another chain
     *
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Can't add events of a different chain
     */
    public function testAddAnotherChain()
    {
        $chain = $this->createEventChain(2);
        $newChain = $this->createEventChain(2);

        $manager = $this->createEventManager();
        $manager->add($chain, $newChain);
    }

    /**
     * Test 'add' method, if supplied chain is partial
     *
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Partial event chain; doesn't contain the genesis event
     */
    public function testWithPartialChainException()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->method('isPartial')->willReturn(true);

        $newChain = $this->createMock(EventChain::class);

        $constructorArgs = array_values($this->createMockDependencies());

        $manager = new EventManager(...$constructorArgs);
        $manager->add($chain, $newChain);
    }

    /**
     * Test 'getSteps' method
     */
    public function testGetSteps()
    {
        $chain = $this->createMock(EventChain::class);
        $dependencies = $this->createMockDependencies();

        $eventManager = new EventManager(...array_values($dependencies));

        $steps = $this->callPrivateMethod($eventManager, 'getSteps', [$chain]);

        $this->assertInternalType('array', $steps);
        
        $this->assertInstanceOf(Step\SyncChains::class, $steps[0]);
        $this->assertInstanceOf(Step\SkipKnownEvents::class, $steps[1]);
        $this->assertInstanceOf(Step\HandleFork::class, $steps[2]);
        $this->assertInstanceOf(Step\ValidateNewEvent::class, $steps[3]);
        $this->assertInstanceOf(Step\StoreResource::class, $steps[4]);
        $this->assertInstanceOf(Step\HandleFailed::class, $steps[5]);
        $this->assertInstanceOf(Step\SaveEvent::class, $steps[6]);
        $this->assertInstanceOf(Step\AnchorEvent::class, $steps[7]);
        $this->assertInstanceOf(Step\TriggerResources::class, $steps[8]);
        $this->assertInstanceOf(Step\Walk::class, $steps[9]);
        $this->assertInstanceOf(Step\Dispatch::class, $steps[10]);
    }
}
