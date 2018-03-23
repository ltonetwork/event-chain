<?php

/**
 * @covers EventChain
 */
class EventChainTest extends \Codeception\Test\Unit
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
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya'
        ]);
        
        $this->assertSame("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U", $chain->getInitialHash());
    }

    public function testGetLastestHash()
    {
        $events = [];
        
        $events[0] = $this->createMock(Event::class);
        $events[0]->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $events[0]->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $events[1] = $this->createMock(Event::class);
        $events[1]->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $events[1]->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => $events
        ]);
        
        $this->assertEquals("J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS", $chain->getLatestHash());
    }
    
    public function testGetLastestHashEmpty()
    {
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya'
        ]);
        
        $this->assertEquals("7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U", $chain->getLatestHash());
    }
    
    public function testGetFirstEvent()
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        
        $chain = EventChain::create()->setValues([
            'events' => [ $event1, $event2 ]
        ]);
        
        $this->assertSame($event1, $chain->getFirstEvent());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testGetFirstEventUnderflow()
    {
        $chain = EventChain::create()->setValues([
            'events' => [ ]
        ]);
        
        $chain->getFirstEvent();
    }

    public function testGetLastEvent()
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        
        $chain = EventChain::create()->setValues([
            'events' => [ $event1, $event2 ]
        ]);
        
        $this->assertSame($event2, $chain->getLastEvent());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testGetLastEventUnderflow()
    {
        $chain = EventChain::create()->setValues([
            'events' => [ ]
        ]);
        
        $chain->getLastEvent();
    }

    public function testIsPartialFalse()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event ]
        ]);
        
        $this->assertFalse($chain->isPartial());
    }
    
    public function testIsPartialTrue()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $chain = EventChain::create()->setValues([
            'id' => '3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj',
            'events' => [ $event ]
        ]);
        
        $this->assertTrue($chain->isPartial());
    }
    
    public function testIsPartialNoEvents()
    {
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ ]
        ]);
        
        $this->assertFalse($chain->isPartial());
    }
    
    public function testValidateNoEvents()
    {
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals(['no events'], $validation->getErrors());
    }
    
    public function testValidateId()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "7oE75kgAjGt84qznVmX6qCnSYjBC8ZGY7JnLkXFfqF3U";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $chain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateIdFail()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "FPipjZ9irhdq2Byq1RWC5yrEvVRFhvZckZPzuaYRDubL";
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $chain = EventChain::create()->setValues([
            'id' => '2JkYmWa9gyT32xT2gWvkGbLHXziw6Qy517KzEvUttigtmM',
            'events' => [ $event ]
        ]);
        
        $validation = $chain->validate();
        
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
        
        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event1, $event2 ]
        ]);
        
        $validation = $chain->validate();
        
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
        
        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event1, $event3 ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals([
            "broken chain; previous of '3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq' is 'J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS', expected '3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj'"
        ], $validation->getErrors());
    }
    
    public function testWithoutEvents()
    {
        $event1 = $this->createMock(Event::class);
        $event2 = $this->createMock(Event::class);
        $identity = $this->createMock(Identity::class);
        
        $chain = EventChain::create()->setValues([
            'events' => [ $event1, $event2 ],
            'identities' => [ $identity ],
            'resources' => [
                'lt:/foos/123',
                'lt:/bars/422'
            ]
        ]);
        
        $emptyChain = $chain->withoutEvents();
        
        $this->assertInstanceOf(EventChain::class, $emptyChain);
        $this->assertNotSame($chain, $emptyChain);
        
        $this->assertEquals($chain->id, $emptyChain->id);
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
        
        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => $events
        ]);
        
        $following = $chain->getEventsAfter($hash);
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
        
        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => $events
        ]);
        
        $chain->getEventsAfter("Aw2Rum85dWFcUKnY6wZPmpoJXK54zENePuLPKjvjhviU");
    }
    
    
    public function testRegisterResource()
    {
        $resource = $this->createMock(Resource::class);
        $resource->expects($this->once())->method('getId')->with(false)->willReturn('lt:/foos/123');
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        
        $chain->registerResource($resource);
        
        $this->assertEquals(['lt:/foos/123'], $chain->resources);
    }
    
    public function testRegisterResourceExisting()
    {
        $resource = $this->createMock(Resource::class);
        $resource->expects($this->once())->method('getId')->with(false)->willReturn('lt:/foos/123');
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        $chain->resources = ['lt:/foos/123', 'lt:/bars/333'];
        
        $chain->registerResource($resource);
        
        $this->assertEquals(['lt:/foos/123', 'lt:/bars/333'], $chain->resources);
    }
    
    public function testRegisterResourceIdentity()
    {
        $resource = $this->createMock(Identity::class);
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->once())->method('set')->with($this->identicalTo($resource));
        
        $chain->registerResource($resource);
        
        $this->assertEquals([], $chain->resources);
    }
}
