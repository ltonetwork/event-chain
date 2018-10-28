<?php

use Jasny\ValidationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use LTO\Account;

/**
 * @covers EventManager
 */
class EventManagerTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
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
     * Create mock dependencies for EventManager
     *
     * @return array
     */
    protected function createMockDependencies(): array
    {
        $node = $this->createConfiguredMock(Account::class, ['getPublicSignKey' => '123']);

        return [
            ResourceFactory::class => $this->createMock(ResourceFactory::class),
            ResourceStorage::class => $this->createMock(ResourceStorage::class),
            DispatcherManager::class => $this->createMock(DispatcherManager::class),
            EventFactory::class => $this->createConfiguredMock(EventFactory::class, ['getNodeAccount' => $node]),
            AnchorClient::class => $this->createMock(AnchorClient::class),
        ];
    }

    /**
     * Create a partial mock EventManager
     * 
     * @param EventChain  $chain
     * @param array|null  $methods
     * @param array       $dependencies
     * @return EventManager|MockObject
     */
    protected function createEventManager(EventChain $chain, ?array $methods = null, array $dependencies = [])
    {
        $constructorArgs = array_values(array_merge($this->createMockDependencies(), $dependencies));

        $eventManager = $this->getMockBuilder(EventManager::class)
            ->setConstructorArgs($constructorArgs)
            ->setMethods($methods)
            ->getMock();

        $this->setPrivateProperty($eventManager, 'chain', $chain);

        return $eventManager;
    }
    
    
    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Event chain doesn't contain the genesis event
     */
    public function testConstructPartialChain()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->method('isPartial')->willReturn(true);

        $constructorArgs = array_values($this->createMockDependencies());
        
        (new EventManager(...$constructorArgs))->with($chain);
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
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn([]);
        $chain->method('getNodesForSystem')->willReturn([]);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn([]);
        
        $manager = $this->createEventManager($chain, ['handleNewEvent']);
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
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn([]);
        $chain->method('getNodesForSystem')->willReturn([]);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn($chainEvents);
        
        $manager = $this->createEventManager($chain, ['handleNewEvent']);
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

        $manager = $this->createEventManager($chain, ['handleNewEvent']);
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
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->willThrowException(new OutOfBoundsException("not found"));
        
        $manager = $this->createEventManager($chain, ['handleNewEvent']);
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
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn($chainEvents);
        
        $manager = $this->createEventManager($chain, ['handleNewEvent', 'handleFailedEvent']);
        $manager->expects($this->never())->method('handleNewEvent');
        $manager->expects($this->once())->method('handleFailedEvent')
            ->with($this->identicalTo($events[1]), $this->isInstanceOf(ValidationResult::class));
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["fork detected; conflict on '{$events[1]->hash}' and '{$chainEvents[1]->hash}'"],
                $validation->getErrors());
    }
    
    public function testAddDispatch()
    {
        $events = $this->createMockEvents();

        $newEvents = $this->createPartialMock(EventChain::class, ['validate']);
        $newEvents->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $newEvents->events = \Jasny\DB\EntitySet::forClass(Event::class, $events);
        $newEvents->expects($this->once())->method('validate')->willReturn(ValidationResult::success());

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('getNodesForSystem')->willReturn(['local']);
        $chain->expects($this->exactly(2))->method('getNodes')
            ->willReturnOnConsecutiveCalls(['local', 'ex1', 'ex2'], ['local', 'ex1', 'ex2', 'ex3']);
        $chain->expects($this->once())->method('getPartialAfter')->willReturn($newEvents);
        $chain->expects($this->once())->method('getEventsAfter')
            ->with("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U")->willReturn([]);

        $dispatcher = $this->createMock(DispatcherManager::class);
        $dispatcher->expects($this->exactly(2))->method('dispatch')
            ->withConsecutive([$newEvents, ['ex1', 'ex2']], [$chain, ['ex3']]);
        
        $manager = $this->createEventManager($chain, ['handleNewEvent'], [DispatcherManager::class => $dispatcher]);
        $manager->expects($this->exactly(2))->method('handleNewEvent')
            ->withConsecutive([$this->identicalTo($events[0])], [$this->identicalTo($events[1])])
            ->willReturn(ValidationResult::success());
        
        $validation = $manager->add($newEvents);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }
    
    
    public function testHandleNewEvent()
    {
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
                
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->once())->method('add')->with($event);
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;
        $chain->expects($this->once())->method('registerResource')->with($this->identicalTo($resource));

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->once())->method('extractFrom')
            ->with($this->identicalTo($event))->willReturn($resource);

        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->once())->method('store')->with($this->identicalTo($resource));
        
        $manager = $this->createEventManager(
            $chain,
            ['applyPrivilegeToResource'],
            [ResourceFactory::class => $resourceFactory, ResourceStorage::class => $resourceStorage]
        );
        $manager->expects($this->once())->method('applyPrivilegeToResource')
            ->with($this->identicalTo($resource), $this->identicalTo($event))->willReturn(ValidationResult::success());
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testHandleNewEventAnchor()
    {
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
                
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->once())->method('add')->with($event);
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;
        $chain->expects($this->once())->method('registerResource')->with($this->identicalTo($resource));
        $chain->expects($this->once())->method('isEventSignedByAccount')
            ->with($this->identicalTo($event))
            ->willReturn(true);
        
        $anchor = $this->createMock(AnchorClient::class);
        $anchor->expects($this->once())->method('submit')->with('3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj');

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->once())->method('extractFrom')
            ->with($this->identicalTo($event))->willReturn($resource);

        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->once())->method('store')->with($this->identicalTo($resource));
        
        $manager = $this->createEventManager(
            $chain,
            ['applyPrivilegeToResource'],
            [
                ResourceFactory::class => $resourceFactory,
                ResourceStorage::class => $resourceStorage,
                AnchorClient::class => $anchor
            ]
        );
        $manager->expects($this->once())->method('applyPrivilegeToResource')
            ->with($this->identicalTo($resource), $this->identicalTo($event))->willReturn(ValidationResult::success());
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testHandleNewEventAuth()
    {
        $error = ValidationResult::error('auth error');
        
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->never())->method('add');
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->once())->method('extractFrom')
            ->with($this->identicalTo($event))->willReturn($resource);

        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->never())->method('store');
                
        $manager = $this->createEventManager(
            $chain,
            ['applyPrivilegeToResource'],
            [ResourceFactory::class => $resourceFactory, ResourceStorage::class => $resourceStorage]
        );
        $manager->expects($this->once())->method('applyPrivilegeToResource')
            ->with($this->identicalTo($resource), $this->identicalTo($event))->willReturn($error);
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["auth error"], $validation->getErrors());
    }
    
    public function testHandleNewEventValidation()
    {
        $error = ValidationResult::error('something is wrong');
        
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn($error);
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->never())->method('add');
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U");
        $chain->events = $eventSet;

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->never())->method('extractFrom');

        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->never())->method('store');
                
        $manager = $this->createEventManager(
            $chain,
            ['applyPrivilegeToResource'],
            [ResourceFactory::class => $resourceFactory, ResourceStorage::class => $resourceStorage]
        );
        $manager->expects($this->never())->method('applyPrivilegeToResource');
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["something is wrong"], $validation->getErrors());
    }
    
    public function testHandleNewEventNotFit()
    {
        $event = $this->createMockEvents()[0];
        $event->expects($this->once())->method('validate')->willReturn(ValidationResult::success());
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->never())->method('add');
        
        $chain = $this->createMock(EventChain::class);
        $chain->method('getLatestHash')->willReturn("J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS");
        $chain->events = $eventSet;

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->never())->method('extractFrom');

        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->never())->method('store');
                
        $manager = $this->createEventManager(
            $chain,
            ['applyPrivilegeToResource'],
            [ResourceFactory::class => $resourceFactory, ResourceStorage::class => $resourceStorage]
        );
        $manager->expects($this->never())->method('applyPrivilegeToResource');
        
        $validation = $manager->handleNewEvent($event);
        
        $this->assertInstanceOf(ValidationResult::class, $validation);
        $this->assertEquals(["event '{$event->hash}' doesn't fit on chain"], $validation->getErrors());
    }
    
    
    public function testHandleFailedEvent()
    {
        [$event, $errorEvent] = $this->createMockEvents();
        
        $eventSet = $this->createMock(Jasny\DB\EntitySet::class);
        $eventSet->expects($this->once())->method('add')->with($errorEvent);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('getEventsAfter')->
            with('7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U')->willReturn([$event]);
        $chain->events = $eventSet;

        $error = ValidationResult::error('something is wrong');

        $eventFactory = $this->createMock(EventFactory::class);
        $eventFactory->expects($this->once())->method('createErrorEvent')
            ->with(['something is wrong'], [$event])->willReturn($errorEvent);
        
        $manager = $this->createEventManager($chain, null, [EventFactory::class => $eventFactory]);
        $manager->handleFailedEvent($event, $error);
    }
    
    
    public function testApplyPrivilegeToResource()
    {
        $event = $this->createMockEvents()[0];
        $event->signkey = "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj";
        
        $identity = $this->createMock(Identity::class);
        $privileges = [ $this->createMock(Privilege::class) ];
        $privilege = $this->createMock(Privilege::class); // consolidated
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->schema = "http://example.com/foo/schema.json#";
        $resource->expects($this->once())->method('applyPrivilege')->with($this->identicalTo($privilege));
        $resource->expects($this->once())->method('setIdentity')->with($this->identicalTo($identity));
        
        $filteredIdentities = $this->createPartialMock(IdentitySet::class, ['getPrivileges']);
        $filteredIdentities->expects($this->once())->method('getPrivileges')
            ->with($this->identicalTo($resource))->willReturn($privileges);
        $filteredIdentities[0] = $identity;
        
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->expects($this->once())->method('filterOnSignkey')->with($event->signkey)
            ->willReturn($filteredIdentities);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('isEmpty')->willReturn(false);
        $chain->identities = $identitySet;
        
        $resourceStorage = $this->createMock(ResourceStorage::class);

        $manager = $this->createEventManager(
            $chain,
            ['consolidatedPrivilege'],
            [ResourceStorage::class => $resourceStorage]
        );
        $manager->expects($this->once())->method('consolidatedPrivilege')
            ->with($this->identicalTo($resource), $this->identicalTo($privileges))->willReturn($privilege);
        
        $manager->applyPrivilegeToResource($resource, $event);
    }

    public function testApplyPrivilegeToResourceInitialIdentity()
    {
        $event = $this->createMockEvents()[0];
        $event->signkey = "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj";
        
        $resource = $this->createMock(Identity::class);
        $resource->schema = "http://example.com/identity/schema.json#";
        $resource->expects($this->never())->method('applyPrivilege');
        $resource->expects($this->never())->method('setIdentity');
        
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->expects($this->never())->method('filterOnSignkey');
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('isEmpty')->willReturn(true);
        $chain->identities = $identitySet;
        
        $manager = $this->createEventManager($chain, ['consolidatedPrivilege']);
        $manager->expects($this->never())->method('consolidatedPrivilege');
        
        $manager->applyPrivilegeToResource($resource, $event);
    }
    
    public function testApplyPrivilegeToResourceNoPrivs()
    {
        $event = $this->createMockEvents()[0];
        $event->signkey = "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj";
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->schema = "http://example.com/foo/schema.json#";
        $resource->expects($this->never())->method('applyPrivilege');
        $resource->expects($this->never())->method('setIdentity');
        
        $filteredIdentities = $this->createMock(IdentitySet::class);
        $filteredIdentities->expects($this->once())->method('getPrivileges')
            ->with($this->identicalTo($resource))->willReturn([]);
        
        $identitySet = $this->createMock(IdentitySet::class);
        $identitySet->expects($this->once())->method('filterOnSignkey')->with($event->signkey)
            ->willReturn($filteredIdentities);
        
        $chain = $this->createMock(EventChain::class);
        $chain->expects($this->once())->method('isEmpty')->willReturn(false);
        $chain->identities = $identitySet;
        
        $manager = $this->createEventManager($chain);
        
        $manager->applyPrivilegeToResource($resource, $event);
    }
    
    
    public function testConsolidatedPrivilege()
    {
        $chain = $this->createMock(EventChain::class);
        
        $resource = $this->createMock(ResourceInterface::class);
        $resource->schema = "http://example.com/foo/schema.json#";
        
        $privileges = [ $this->createMock(Privilege::class) ];
        
        $manager = $this->createEventManager($chain);
        
        $this->callPrivateMethod($manager, 'consolidatedPrivilege', [$resource, $privileges]);
    }
}
