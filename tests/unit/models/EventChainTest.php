<?php

/**
 * @covers EventChain
 */
class EventChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testCreateId()
    {
        $base58 = new StephenHill\Base58();
        
        $signkey = $base58->decode("8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ");
        
        $signkeyHashed = substr(Keccak::hash(sodium\crypto_generichash($signkey, null, 32), 256), 0, 40);
        $this->assertEquals("2a66ea3f3bd86d60e52eccc1a71de7efe927514f", $signkeyHashed);
        
        $packed = pack('CH16H40', 1, '0000000000000000', $signkeyHashed);
        $chksum = substr(Keccak::hash(sodium\crypto_generichash($packed), 256), 0, 8);
        $this->assertEquals("c2fe2e3d", $chksum);
        
        $idBinary = pack('CH16H40H8', 1, '0000000000000000', $signkeyHashed, $chksum);
        $this->assertEquals(33, strlen($idBinary));
        
        $id = $base58->encode($idBinary);
        $this->assertEquals('JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya', $id);
    }
    
    public function testGetInitialHash()
    {
        $eventChain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya'
        ]);
        
        $this->assertSame("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U", $eventChain->getInitialHash());
    }

    public function testGetFirstEvent()
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        
        $eventChain = EventChain::create()->setValues([
            'events' => [ $event1, $event2 ]
        ]);
        
        $this->assertSame($event1, $eventChain->getFirstEvent());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testGetFirstEventUnderflow()
    {
        $eventChain = EventChain::create()->setValues([
            'events' => [ ]
        ]);
        
        $eventChain->getFirstEvent();
    }

    public function testValidateNoEvents()
    {
        $eventChain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals(['no events'], $validation->getErrors());
    }
    
    public function testValidateId()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $eventChain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateIdFail()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "FPipjZ9irhdq2Byq1RWC5yrEvVRFhvZckZPzuaYRDubL";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $eventChain = EventChain::create()->setValues([
            'id' => '2JkYmWa9gyT32xT2gWvkGbLHXziw6Qy517KzEvUttigtmM',
            'events' => [ $event ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals(['invalid id'], $validation->getErrors());
    }
    
    public function testValidateIntegrity()
    {
        $event1 = $this->createMock(Event::class);
        $event1->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $event1->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event1->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $event2 = $this->createMock(Event::class);
        $event2->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $event2->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event2->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        $eventChain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event1, $event2 ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateIntegrityFailed()
    {
        $event1 = $this->createMock(Event::class);
        $event1->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $event1->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event1->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $event3 = $this->createMock(Event::class);
        $event3->previous = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        $event3->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event3->hash = "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq";
        
        $eventChain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event1, $event3 ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals([
            "broken chain; previous of '3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq' is 'J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS', expected '3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj'"
        ], $validation->getErrors());
    }
    
    public function testWithoutEvents()
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        $identity = $this->createMock(Identity::class);
        
        $eventChain = EventChain::create()->setValues([
            'events' => [ $event1, $event2 ],
            'identities' => [ $identity ],
            'resources' => [
                'lt:/foos/123',
                'lt:/bars/422'
            ]
        ]);
        
        $emptyChain = $eventChain->withoutEvents();
        
        $this->assertInstanceOf(EventChain::class, $emptyChain);
        $this->assertNotSame($eventChain, $emptyChain);
        
        $this->assertEquals($eventChain->id, $emptyChain->id);
        $this->assertCount(0, $emptyChain->events);
        $this->assertCount(0, $emptyChain->identities);
        $this->assertCount(0, $emptyChain->resources);
    }
    
    public function getEventsAfterProvider()
    {
        return [
            [
                "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U",
                [
                    "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj",
                    "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS",
                    "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq"
                ]
            ],
            [
                "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj",
                [
                    "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS",
                    "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq"
                ]
            ],
            [
                "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS",
                [
                    "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq"
                ]
            ],
            [
                "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq",
                []
            ]
        ];
    }
    
    /**
     * @dataProvider getEventsAfterProvider
     * 
     * @param string   $hash
     * @param string[] $expected
     */
    public function testGetEventsAfter($hash, $expected)
    {
        $events = [];
        
        $events[0] = $this->createMock(Event::class);
        $events[0]->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $events[0]->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $events[1] = $this->createMock(Event::class);
        $events[1]->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $events[1]->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        $events[2] = $this->createMock(Event::class);
        $events[2]->previous = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        $events[2]->hash = "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq";
        
        $eventChain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => $events
        ]);
        
        $following = $eventChain->getEventsAfter($hash);
        $this->assertInternalType('array', $following);
        
        $actual = array_map(function($event) { return $event->hash; }, $following);
        $this->assertSame($expected, $actual);
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetEventsAfterOutOfBounds()
    {
        $events = [];
        
        $events[0] = $this->createMock(Event::class);
        $events[0]->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $events[0]->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $events[1] = $this->createMock(Event::class);
        $events[1]->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $events[1]->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        $events[2] = $this->createMock(Event::class);
        $events[2]->previous = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        $events[2]->hash = "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq";
        
        $eventChain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => $events
        ]);
        
        $eventChain->getEventsAfter("Aw2Rum85dWFcUKnY6wZPmpoJXK54zENePuLPKjvjhviU");
    }
}
