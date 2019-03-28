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
        
        $events[0] = new Event();
        $events[0]->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $events[0]->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $events[1] = new Event();
        $events[1]->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $events[1]->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        return $events;
    }
    
    
    public function testCreateErrorEvent()
    {
        $events = $this->createMockEvents();
        $account = $this->createMock(Account::class);
        $account->expects($this->once())->method('signEvent');
        
        $factory = new EventFactory($account);
        $event = $factory->createErrorEvent('Something went wrong', $events);

        $body = json_decode(base58_decode($event->body));

        // @todo: need to use actual account to validate signing
        $this->assertEquals(['Something went wrong'], $body->message);
        $this->assertCount(2, $body->events);
        $this->assertSame('3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj', $body->events[0]->hash);
        $this->assertSame('J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS', $body->events[1]->hash);        
        $this->assertAttributeEquals('7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U', 'previous', $event);
        $this->assertAttributeEquals('', 'hash', $event);
    }

    /**
     * Test 'getNodeAccount' method
     */
    public function testGetNodeAccount()
    {
        $account = $this->createMock(Account::class);        
        $factory = new EventFactory($account);

        $result = $factory->getNodeAccount();

        $this->assertSame($account, $result);
    }
}
