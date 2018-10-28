<?php

use LTO\Account;

/**
 * @covers EventFactory
 */
class EventFactoryTest extends \Codeception\Test\Unit
{
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
     * Create a partial mock EventFactory
     * 
     * @param array|null                     $methods
     * @param Account                        $account
     * @return DispatcherManager|MockObject
     */
    protected function createEventFactory(
        $methods = null,
        Account $account = null
    ) {
        return $this->getMockBuilder(EventFactory::class)
            ->setConstructorArgs([
                $account ?: $this->createMock(Account::class)
            ])
            ->setMethods($methods)
            ->getMock();
    }
    
    
    public function testCreateErrorEvent()
    {
        $events = $this->createMockEvents();
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('signEvent');
        
        $factory = $this->createEventFactory(null, $account);
        $event = $factory->createErrorEvent('Something went wrong', $events);
        
        // @todo: need to use actual account to validate signing
        $this->assertAttributeEquals('7xG', 'body', $event);
        $this->assertAttributeEquals('7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U', 'previous', $event);
        $this->assertAttributeEquals('', 'hash', $event);
    }
}
