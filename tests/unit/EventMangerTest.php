<?php

use Jasny\ValidationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers EventManager
 */
class EventMangerTest extends \Codeception\Test\Unit
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
        
        new EventManger($chain, $resourceManager);
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
        $chain->expects($this->once())->method('getEventsAfter')->willReturn([]);
        
        $resourceManager = $this->createMock(ResourceManager::class);
        
        $manager = $this->getMockBuilder(EventManger::class)
            ->setConstructorArgs([$chain, $resourceManager])
            ->setMethods(['handleNewEvent'])
            ->getMock();
        $manager->expects($this->exactly(2))->method('handleNewEvent')
            ->withConsecutive([$this->identicalTo($events[0])], [$this->identicalTo($events[1])])
            ->willReturn(ValidationResult::success());
        
        $manager->add($newEvents);
    }
}
