<?php

use Jasny\ValidationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use LTO\Account;

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
            EventChainGateway::class => $this->createMock(EventChainGateway::class)
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
     * Test 'with' method
     */
    public function testWith()
    {
        $chain = $this->createMock(EventChain::class);
        $manager = $this->createEventManager();

        $result = $manager->with($chain);

        $this->assertInstanceOf(EventManager::class, $result);
        $this->assertAttributeEquals($chain, 'chain', $result);
    }
    
    /**
     * Test 'with' method, if chain is already set
     * 
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Chain already set
     */
    public function testWithChainExistsException()
    {
        $chain = $this->createMock(EventChain::class);
        $newChain = $this->createMock(EventChain::class);

        $manager = $this->createEventManager();
        $this->setPrivateProperty($manager, 'chain', $chain);

        $manager->with($newChain);
    }
    
    /**
     * Test 'with' method, if supplied chain is partial
     * 
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Event chain doesn't contain the genesis event
     */
    public function testWithPartialChainException()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->method('isPartial')->willReturn(true);

        $constructorArgs = array_values($this->createMockDependencies());
        
        (new EventManager(...$constructorArgs))->with($chain);
    }

    /**
     * Test 'add' method
     */
    public function testAdd()
    {
        $chain = $this->createMock(EventChain::class);
        $newChain = $this->createMock(EventChain::class);

        $manager = $this->createEventManager(['getSteps']);
        $this->setPrivateProperty($manager, 'chain', $chain);

        $steps = [
            function(EventChain $events, ValidationResult $validation) use ($newChain): EventChain {
                $validation->addError('Called first');
                $this->assertSame($newChain, $events);
                
                return $newChain;
            },
            function(EventChain $events, ValidationResult $validation) use ($newChain): EventChain {
                $validation->addError('Called second');
                $this->assertSame($newChain, $events);

                return $newChain;
            },
        ];

        $manager->expects($this->once())->method('getSteps')->willReturn($steps);

        $result = $manager->add($newChain);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame(['Called first', 'Called second'], $result->getErrors());
    }

    /**
     * Test 'add' method, if no chain is set
     *
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Chain not set; use the `withChain()` method.
     */
    public function testAddNoChainException()
    {
        $chain = $this->createMock(EventChain::class);
        $manager = $this->createEventManager();

        $manager->add($chain);
    }
}
