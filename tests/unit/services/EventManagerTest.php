<?php

use Jasny\ValidationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AddEventStep as Step;

/**
 * @covers \EventManager
 */
class EventManagerTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;

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
            DispatcherManager::class => $this->createMock(DispatcherManager::class),
            EventFactory::class => $this->createMock(EventFactory::class),
            AnchorClient::class => $this->createMock(AnchorClient::class),
            EventChainGateway::class => $this->createMock(EventChainGateway::class),
            ConflictResolver::class => $this->createMock(ConflictResolver::class),
        ];
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
     * Create stubs for the steps.
     *
     * @param string|null $firstError
     * @param string|null $secondError
     * @param EventChain  $chain
     * @return callable[]
     */
    protected function stubSteps(?string $firstError, ?string $secondError, EventChain $newChain): array
    {
        return [
            function(EventChain $events, ValidationResult $validation) use ($firstError, $newChain): EventChain {
                if ($firstError !== null) {
                    $validation->addError($firstError);
                }

                $this->assertSame($newChain, $events);

                return $newChain;
            },
            function(EventChain $events, ValidationResult $validation) use ($secondError, $newChain): EventChain {
                if ($secondError !== null) {
                    $validation->addError($secondError);
                }

                $this->assertSame($newChain, $events);

                return $newChain;
            },
        ];
    }


    public function addProvider()
    {
        return [
            ['First error', 'secondError'],
            []
        ];
    }

    /**
     * Test 'add' method
     * @dataProvider addProvider
     */
    public function testAdd(string ...$errors)
    {
        $chain = $this->createMock(EventChain::class);
        $newChain = $this->createMock(EventChain::class);

        $manager = $this->createEventManager(['getSteps']);

        $manager->expects($this->once())->method('getSteps')
            ->willReturn($this->stubSteps($errors[0] ?? null, $errors[1] ?? null, $newChain));

        $result = $manager->add($chain, $newChain);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame($errors, $result->getErrors());
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

    public function testGetSteps()
    {
        $chain = $this->createMock(EventChain::class);
        $dependencies = $this->createMockDependencies();

        $eventManager = new EventManager(...array_values($dependencies));

        $steps = $this->callPrivateMethod($eventManager, 'getSteps', [$chain]);

        $this->assertInternalType('array', $steps);
        
        $this->assertInstanceOf(Step\ValidateInput::class, $steps[0]);
        $this->assertInstanceOf(Step\SyncChains::class, $steps[1]);
        $this->assertInstanceOf(Step\SkipKnownEvents::class, $steps[2]);
        $this->assertInstanceOf(Step\HandleFork::class, $steps[3]);
        $this->assertInstanceOf(Step\ValidateNewEvent::class, $steps[4]);
        $this->assertInstanceOf(Step\StoreResource::class, $steps[5]);
        $this->assertInstanceOf(Step\HandleFailed::class, $steps[6]);
        $this->assertInstanceOf(Step\SaveEvent::class, $steps[7]);
        $this->assertInstanceOf(Step\Walk::class, $steps[8]);
        $this->assertInstanceOf(Step\Dispatch::class, $steps[9]);
        $this->assertInstanceOf(Step\TriggerResourceServices::class, $steps[10]);
    }
}
