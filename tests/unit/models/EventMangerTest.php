<?php

use Jasny\ValidationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers EventManager
 */
class EventManagerTest extends \Codeception\Test\Unit
{
    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Event chain doesn't contain the genesis event
     */
    public function testConstructPartialChain()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->method('isPartial')->willReturn(true);
        
        $resourceManager = $this->createMock(ResourceManager::class);
        
        new EventManager($chain, $resourceManager);
    }
    
    /**
     * @return Event[]|MockObject[]
     */
    protected function createMockEvents()
    {
        $events = [];
        
        $events[0] = $this->createMock(Event::class);
        $events[0]->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $events[0]->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $events[1] = $this->createMock(Event::class);
        $events[1]->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $events[1]->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        return $events;
    }
    
    /**
     * Create a partial mock EventManager
     * 
     * @param EventChain      $chain
     * @param ResourceManager $resourceManager
     * @param array|null      $methods
     * @return EventManager|MockObject
     */
    protected function createEventManager(EventChain $chain, ResourceManager $resourceManager = null, $methods = null)
    {
        if (!isset($resourceManager)) {
            $resourceManager = $this->createMock(ResourceManager::class);
        }
        
        return $this->getMockBuilder(EventManager::class)
            ->setConstructorArgs([$chain, $resourceManager])
            ->setMethods($methods)
            ->getMock();
    }
    
    public function testAdd()
    {
        $events = $this->createMockEvents();
        
        $newEvents = $this->createPartialMock(EventChain::class, ['validate']);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->events = \Jasny\DB\EntitySet::forClass(Event::class, $events);
        $newEvents->expects($this->once())->method('validate')->willReturn(ValidationResult::success());

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn([]);
        
        $manager = $this->createEventManager($chain, null, ['handleNewEvent']);
        $manager->expects($this->exactly(2))->method('handleNewEvent')
            ->withConsecutive([$this->identicalTo($events[0])], [$this->identicalTo($events[1])])
            ->willReturn(ValidationResult::success());
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }

    public function testAddSkip()
    {
        $events = $this->createMockEvents();
        
        $newEvents = $this->createPartialMock(EventChain::class, ['validate']);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->events = \Jasny\DB\EntitySet::forClass(Event::class, $events);
        $newEvents->expects($this->once())->method('validate')->willReturn(ValidationResult::success());

        $chainEvents = $this->createMockEvents();
        unset($chainEvents[1]);

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn($chainEvents);
        
        $manager = $this->createEventManager($chain, null, ['handleNewEvent']);
        $manager->expects($this->once())->method('handleNewEvent')
            ->with($this->identicalTo($events[1]))
            ->willReturn(ValidationResult::success());
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testAddDifferentChain()
    {
        $newEvents = $this->createMock(EventChain::class);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        
        $chain = $this->createMock(EventChain::class);
        $chain->id = "2JkYmWa9gyT32xT2gWvkGbLHXziw6Qy517KzEvUttigtmM";

        $manager = $this->createEventManager($chain);
        
        $manager->add($newEvents);
    }
    
    public function testAddValidationFailure()
    {
        $error = ValidationResult::error('something is wrong');
        
        $newEvents = $this->createMock(EventChain::class);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->expects($this->once())->method('validate')->willReturn($error);
        
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";

        $manager = $this->createEventManager($chain, null, ['handleNewEvent']);
        $manager->expects($this->never())->method('handleNewEvent');
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(['something is wrong'], $validation->getErrors());
    }
    
    public function testAddOutOfBounds()
    {
        $events = $this->createMockEvents();
        
        $newEvents = $this->createPartialMock(EventChain::class, ['validate']);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->events = \Jasny\DB\EntitySet::forClass(Event::class, $events);
        $newEvents->expects($this->once())->method('validate')->willReturn(ValidationResult::success());

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->willThrowException(new OutOfBoundsException("not found"));
        
        $manager = $this->createEventManager($chain, null, ['handleNewEvent']);
        $manager->expects($this->never())->method('handleNewEvent');
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["events don't fit on chain, '{$events[0]->previous}' not found"],
                $validation->getErrors());
    }
    
    public function testAddFork()
    {
        $events = $this->createMockEvents();
        
        $newEvents = $this->createPartialMock(EventChain::class, ['validate']);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->events = \Jasny\DB\EntitySet::forClass(Event::class, $events);
        $newEvents->expects($this->once())->method('validate')->willReturn(ValidationResult::success());

        $chainEvents = $this->createMockEvents();
        $chainEvents[1]->hash = "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq";

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn($chainEvents);
        
        $manager = $this->createEventManager($chain, null, ['handleNewEvent']);
        $manager->expects($this->never())->method('handleNewEvent');
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["fork detected; conflict on '{$events[1]->hash}' and '{$chainEvents[1]->hash}'"],
                $validation->getErrors());
    }
    
    
    public function testHandleNewEvent()
    {
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
                
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->once())->method('add')->with($event);
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;

        $resource = $this->createMock(Resource::class);
        
        $resourceManager = $this->createMock(ResourceManager::class);
        $resourceManager->expects($this->once())->method('extractFrom')
            ->with($this->identicalTo($event))->willReturn($resource);
                
        $manager = $this->createEventManager($chain, $resourceManager, ['addResource']);
        $manager->expects($this->once())->method('addResource')->with($this->identicalTo($resource));
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testHandleEventValidation()
    {
        $error = ValidationResult::error('something is wrong');
        
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn($error);
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->never())->method('add');
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;

        $resourceManager = $this->createMock(ResourceManager::class);
        $resourceManager->expects($this->never())->method('extractFrom');
                
        $manager = $this->createEventManager($chain, $resourceManager, ['addResource']);
        $manager->expects($this->never())->method('addResource');
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["something is wrong"], $validation->getErrors());
    }
    
    public function testHandleEventNotFit()
    {
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->never())->method('add');
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS");
        $chain->events = $eventSet;

        $resourceManager = $this->createMock(ResourceManager::class);
        $resourceManager->expects($this->never())->method('extractFrom');
        $resourceManager->expects($this->never())->method('store');
                
        $manager = $this->createEventManager($chain, $resourceManager, ['addResource']);
        $manager->expects($this->never())->method('addResource');
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["event '{$event->hash}' doesn't fit on chain"], $validation->getErrors());
    }
    
    
    public function testAddResource()
    {
        $event = $this->createMockEvents()[0];
        $event->signkey = "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj";
        
        $privilege = $this->createMock(Privilege::class);
        
        $resource = $this->createMock(Resource::class);
        $resource->expects($this->once())->method('applyPrivilege')->with($this->identicalTo($privilege));
        
        $filteredIdentities = $this->createMock(IdentitySet::class);
        $filteredIdentities->expects($this->once())->method('getPrivilege')
            ->with($this->identicalTo($resource))->willReturn($privilege);
        
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->expects($this->once())->method('filterOnSignkey')->with($event->signkey)
            ->willReturn($filteredIdentities);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('registerResource')->with($this->identicalTo($resource));
        $chain->identities = $identitySet;
        
        $resourceManager = $this->createMock(ResourceManager::class);
        $resourceManager->expects($this->once())->method('store')->with($this->identicalTo($resource));

        $manager = $this->createEventManager($chain, $resourceManager);
        
        $manager->addResource($resource, $event);
    }
    
    public function testAddResourceNoPrivs()
    {
        $event = $this->createMockEvents()[0];
        $event->signkey = "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj";
        
        $resource = $this->createMock(Resource::class);
        $resource->expects($this->never())->method('applyPrivilege');
        
        $filteredIdentities = $this->createMock(IdentitySet::class);
        $filteredIdentities->expects($this->once())->method('getPrivilege')
            ->with($this->identicalTo($resource))->willReturn(null);
        
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->expects($this->once())->method('filterOnSignkey')->with($event->signkey)
            ->willReturn($filteredIdentities);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->never())->method('registerResource');
        $chain->identities = $identitySet;
        
        $resourceManager = $this->createMock(ResourceManager::class);
        $resourceManager->expects($this->never())->method('store');

        $manager = $this->createEventManager($chain, $resourceManager);
        
        $manager->addResource($resource, $event);
    }
}
