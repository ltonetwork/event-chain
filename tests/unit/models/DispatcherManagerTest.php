<?php

use LTO\Account;
use Psr\Log\LoggerInterface;

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
     * @return DispatcherManager|MockObject
     */
    protected function createDispatcherManager(
        $methods = null,
        Dispatcher $dispatcher = null,
        Account $account = null
    ) {
        return $this->getMockBuilder(DispatcherManager::class)
            ->setConstructorArgs([
                $dispatcher ?: $this->createMock(Dispatcher::class),
                $account ?: $this->createMock(Account::class),
                $this->createMock(LoggerInterface::class)
            ])
            ->setMethods($methods)
            ->getMock();
    }
    
    
    public function testAddDispatchSuccess()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->events = $this->createMockEvents();
        $chain->method('getLastEvent')->willReturn($chain->events[count($chain->events) - 1]);
        $chain->method('isEventSignedByAccount')->willReturn(true);

        $to = ['ex1', 'ex2'];
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())->method('queue')->with($chain, $to);

        $manager = $this->createDispatcherManager(null, $dispatcher);
        $manager->dispatch($chain, $to);
    }

    public function testAddDispatchSkip()
    {
        $chain = $this->createMock(EventChain::class);
        $chain->id = "JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya";
        $chain->events = $this->createMockEvents();
        $chain->method('getLastEvent')->willReturn($chain->events[count($chain->events) - 1]);
        $chain->method('isEventSignedByAccount')->willReturn(false);

        $to = ['ex1', 'ex2'];
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->never())->method('queue');

        $manager = $this->createDispatcherManager(null, $dispatcher);
        $manager->dispatch($chain, $to);
    }
}
