<?php

use LTO\Account;

/**
 * @covers DispatcherManager
 */
class DispatcherManagerTest extends \Codeception\Test\Unit
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
        $events[1]->signkey = "node_sign_key";
        
        return $events;
    }
    
    /**
     * Create a partial mock DispatcherManager
     * 
     * @param array|null                     $methods
     * @param Dispatcher                     $dispatcher
     * @param Account                        $account
     * @param ResourceFactory                $resourceFactory
     * @return DispatcherManager|MockObject
     */
    protected function createDispatcherManager(
        $methods = null,
        Dispatcher $dispatcher = null,
        Account $account = null,
        ResourceFactory $resourceFactory = null
    ) {
        return $this->getMockBuilder(DispatcherManager::class)
            ->setConstructorArgs([
                $dispatcher ?: $this->createMock(Dispatcher::class),
                $account ?: $this->createMock(Account::class),
                $resourceFactory ?: $this->createMock(ResourceFactory::class),
            ])
            ->setMethods($methods)
            ->getMock();
    }
    
    
    public function testAddDispatchNormalEvent()
    {
        $events = $this->createMockEvents();
        $lastEvent = $events[count($events) - 1];
        $to = ['ex1', 'ex2'];

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn($to);
        
        $chainLastEvent = $this->createMock(EventChain::class);
        $chainLastEvent->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chainLastEvent->events = $lastEvent;
        $chain->method('getLastEvent')->willReturn($lastEvent);
        $chain->method('withEvents')->willReturn($chainLastEvent);
        $chain->method('hasSystemKeyForIdentity')->willReturn(false);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())->method('queue')->with($chainLastEvent, $to);

        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getPublicSignKey')->willReturn('node_sign_key');
        
        $manager = $this->createDispatcherManager(null, $dispatcher, $account);
        
        $manager->dispatch($chain);
    }
    
    public function testAddDispatchIdentityEvent()
    {
        $events = $this->createMockEvents();
        $lastEvent = $events[count($events) - 1];
        $to = ['ex1', 'ex2'];;

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn($to);
        $chain->method('getLastEvent')->willReturn($lastEvent);
        $chain->method('hasSystemKeyForIdentity')->willReturn(false);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())->method('queue')->with($chain, $to);

        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('getPublicSignKey')->willReturn('node_sign_key');
        
        $resource = $this->createMock(Identity::class);
        $resourceFactory = $this->createMock(ResourceFactory::class);
        $resourceFactory->expects($this->once())->method('extractFrom')
            ->with($this->identicalTo($lastEvent))->willReturn($resource);
        
        $manager = $this->createDispatcherManager(null, $dispatcher, $account, $resourceFactory);
        
        $manager->dispatch($chain);
    }

    public function testAddDispatchOtherSignKey()
    {
        $events = $this->createMockEvents();
        $lastEvent = $events[count($events) - 1];
        $to = ['ex1', 'ex2'];

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn($to);

        $chainLastEvent = $this->createMock(EventChain::class);
        $chainLastEvent->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chainLastEvent->events = $lastEvent;
        $chain->method('getLastEvent')->willReturn($lastEvent);
        $chain->method('withEvents')->willReturn($chainLastEvent);
        $chain->method('hasSystemKeyForIdentity')->willReturn(false);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->never())->method('queue');

        $account = $this->createMock(Account::class);
        $account->expects($this->exactly(2))->method('getPublicSignKey')->willReturn('other_sign_key');

        $manager = $this->createDispatcherManager(null, $dispatcher, $account);

        $manager->dispatch($chain);
    }

    public function testAddDispatchIdentityWithSystemKey()
    {
        $events = $this->createMockEvents();
        $lastEvent = $events[count($events) - 1];
        $to = ['ex1', 'ex2'];

        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->method('isEmpty')->willReturn(false);
        $chain->method('isPartial')->willReturn(false);
        $chain->method('getNodes')->willReturn($to);

        $chainLastEvent = $this->createMock(EventChain::class);
        $chainLastEvent->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chainLastEvent->events = $lastEvent;
        $chain->method('getLastEvent')->willReturn($lastEvent);
        $chain->method('withEvents')->willReturn($chainLastEvent);
        $chain->method('hasSystemKeyForIdentity')->willReturn(true);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())->method('queue')->with($chainLastEvent, $to);

        $account = $this->createMock(Account::class);
        $account->expects($this->exactly(2))->method('getPublicSignKey')->willReturn('other_sign_key');

        $manager = $this->createDispatcherManager(null, $dispatcher, $account);

        $manager->dispatch($chain);
    }
}
