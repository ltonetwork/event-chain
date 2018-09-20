<?php

use Jasny\DB\EntitySet;
use kornrunner\Keccak;
use LTO\Account;

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
        
        $signkey = $base58->decode("FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y");
        
        $signkeyHashed = substr(Keccak::hash(sodium_crypto_generichash($signkey, null, 32), 256), 0, 40);
        $this->assertEquals("cfe2a4058d1329ffa541554fc0ce58c8376c7782", $signkeyHashed);
        
        $packed = pack('CH16H40', EventChain::ADDRESS_VERSION, '0000000000000000', $signkeyHashed);
        $chksum = substr(Keccak::hash(sodium_crypto_generichash($packed), 256), 0, 8);
        $this->assertEquals("ebd9a5be", $chksum);
        
        $idBinary = pack('CH16H40H8', EventChain::ADDRESS_VERSION, '0000000000000000', $signkeyHashed, $chksum);
        $this->assertEquals(33, strlen($idBinary));
        
        $id = $base58->encode($idBinary);
        $this->assertEquals('L1hGimV7Pp2CWTUViTuxakPRSq61ootWsh9FuLrU35Lay', $id);
    }
    
    public function testGetInitialHash()
    {
        $chain = EventChain::create()->setValues([
            'id' => 'CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f'
        ]);
        
        $this->assertSame("7juAGSAfJJ2Th9SXGpm3u9XcLtMZzFaExbnCrnUAi1kn", $chain->getInitialHash());
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
        
        $this->assertEquals("BRhevpwYsXv7LD1N4kodG7P6fJrRhPPxqFe4RDq8MwJv", $chain->getLatestHash());
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

    public function testIsEmptyTrue()
    {
        $chain = new EventChain();
        
        $this->assertTrue($chain->isEmpty());
    }

    public function testIsEmptyFalse()
    {
        $chain = new EventChain();
        $chain->events[] = $this->createMock(Event::class);
        
        $this->assertFalse($chain->isEmpty());
    }
    
    public function testIsPartialFalse()
    {
        $event = $this->createMock(Event::class);
        $event->previous = "BRhevpwYsXv7LD1N4kodG7P6fJrRhPPxqFe4RDq8MwJv";
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
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
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
        $event->previous = "FYAWXTgi4oWLWmNtEuNQnAaeMjM9oT7iavzrGKmMoVAw";
        $event->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        
        $chain = EventChain::create()->setValues([
            'id' => '2bGCW3XbfLmSRhotYzcUgqiomiiFLSXKDU43jLMNaf29UXTkpkn2PfvyZkF8yx',
            'events' => [ $event ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertSame([], $validation->getErrors());
    }

    public function invalidIdProvider()
    {
        return [
            ['2bGCW3XbfLmSRhotYzcUgqiomiiFLSXKDU43jLMNaf29UXTkpkn2PfvyZkF8yx']/*,
            ['2ar3wSjTm1fA33qgckZ5Kxn1x89gKKGi6TJsZjRoqb7sjUE8GZXjLaYCbCa2GX'] TODO: Fix this 'wrong' case */
        ];
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function testValidateIdFail($id)
    {
        $event = $this->createMock(Event::class);
        $event->previous = "FYAWXTgi4oWLWmNtEuNQnAaeMjM9oT7iavzrGKmMoVAw";
        $event->signkey = "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP";
        
        $chain = EventChain::create()->setValues([
            'id' => $id,
            'events' => [ $event ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertSame(['invalid id'], $validation->getErrors());
    }
    
    public function testValidateIntegrity()
    {
        $event1 = $this->createMock(Event::class);
        $event1->previous = "3NTzfLcXq1D5BRzhj9EyVbmAcLsz1pa6ZjdxRySbYze1";
        $event1->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event1->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $event2 = $this->createMock(Event::class);
        $event2->previous = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        $event2->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event2->hash = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        
        $chain = EventChain::create()->setValues([
            'id' =>  '2ar3wSjTm1fA33qgckZ5Kxn1x89gKKGi6TJsZjRoqb7sjUE8GZXjLaYCbCa2GX',
            'events' => [ $event1, $event2 ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateIntegrityFailed()
    {
        $event1 = $this->createMock(Event::class);
        $event1->previous = "3NTzfLcXq1D5BRzhj9EyVbmAcLsz1pa6ZjdxRySbYze1";
        $event1->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event1->hash = "3yMApqCuCjXDWPrbjfR5mjCPTHqFG8Pux1TxQrEM35jj";
        
        $event3 = $this->createMock(Event::class);
        $event3->previous = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        $event3->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        $event3->hash = "3HZd1nBeva2fLUUEygGakdCQr84dcUz6J3wGTUsHdnhq";
        
        $chain = EventChain::create()->setValues([
            'id' =>  '2ar3wSjTm1fA33qgckZ5Kxn1x89gKKGi6TJsZjRoqb7sjUE8GZXjLaYCbCa2GX',
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
    
    public function testGetNodes()
    {
        $identity1 = $this->createMock(Identity::class);
        $identity1->node = 'node1';
        $identity2 = $this->createMock(Identity::class);
        $identity2->node = 'node2';

        $chain = EventChain::create()->setValues(['identities' => [$identity1, $identity2]]);
        $this->assertEquals(['node1', 'node2'], $chain->getNodes());
        
        $chain->setValues(['identities' => []]);
        $this->assertEquals([], $chain->getNodes());
    }
    
    public function getEventsAfterProvider()
    {
        return [
            [
                "BRhevpwYsXv7LD1N4kodG7P6fJrRhPPxqFe4RDq8MwJv",
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
        $events[0]->previous = "BRhevpwYsXv7LD1N4kodG7P6fJrRhPPxqFe4RDq8MwJv";
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
        $events[0]->previous = "BRhevpwYsXv7LD1N4kodG7P6fJrRhPPxqFe4RDq8MwJv";
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
        $resource = $this->createMock(ExternalResource::class);
        $resource->expects($this->once())->method('getId')->willReturn('lt:/foos/123?v=22');
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        $chain->comments = $this->createMock(EntitySet::class);
        $chain->comments->expects($this->never())->method('add');
        
        $chain->registerResource($resource);
        
        $this->assertEquals(['lt:/foos/123'], $chain->resources);
    }
    
    public function testRegisterResourceExisting()
    {
        $resource = $this->createMock(ExternalResource::class);
        $resource->expects($this->once())->method('getId')->willReturn('lt:/foos/123?v=22');
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        $chain->comments = $this->createMock(EntitySet::class);
        $chain->comments->expects($this->never())->method('add');
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
        $chain->comments = $this->createMock(EntitySet::class);
        $chain->comments->expects($this->never())->method('add');
        
        $chain->registerResource($resource);
        
        $this->assertEquals([], $chain->resources);
    }
    
    public function testRegisterResourceComment()
    {
        $resource = $this->createMock(Comment::class);
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        $chain->comments = $this->createMock(EntitySet::class);
        $chain->comments->expects($this->once())->method('add')->with($this->identicalTo($resource));
        
        $chain->registerResource($resource);
        
        $this->assertEquals([], $chain->resources);
    }

    public function testHasSystemKeyForIdentity()
    {
        $identities = [
            [
                "id" => "1",
                "signkeys" => [
                    "user" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ",
                    "system" => "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"
                ]
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "user" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG",
                    "system" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"
                ]
            ]
        ];

        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'identities' => $identities
        ]);

        $this->assertTrue($chain->hasSystemKeyForIdentity("8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ", "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"));
        $this->assertTrue($chain->hasSystemKeyForIdentity("4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG", "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"));
        $this->assertFalse($chain->hasSystemKeyForIdentity("8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ", "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"));
    }
    
    public function testIsEventSignedByAccount()
    {
        $identities = [
            [
                "id" => "1",
                "signkeys" => [
                    "user" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ",
                    "system" => "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"
                ]
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "user" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG",
                    "system" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"
                ]
            ]
        ];

        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'identities' => $identities
        ]);
        
        $account = $this->createMock(Account::class);
        $account->expects($this->exactly(3))->method('getPublicSignKey')
            ->willReturnOnConsecutiveCalls('fake', 'fake', 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y');
        
        $event = new Event();
        
        // key doesn't exist
        $event->signkey = 'foo';
        $this->assertFalse($chain->isEventSignedByAccount($event, $account));
        
        // directly check if signed by the account
        $event->signkey = 'fake';
        $this->assertTrue($chain->isEventSignedByAccount($event, $account));
        
        // check if any of the identities of the account signed it
        $event->signkey = '4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG';
        $this->assertTrue($chain->isEventSignedByAccount($event, $account));
    }

    public function testIsEventSentFromNode()
    {
        $identities = [
            [
                "id" => "1",
                "signkeys" => [
                    "user" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ"
                ],
                'node' => 'node1'
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "user" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG"
                ],
                'node' => 'node2'
            ]
        ];

        $chain = EventChain::create()->setValues([
            'id' =>  'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'identities' => $identities
        ]);
                
        $event = new Event();
        
        // key doesn't exist
        $event->signkey = 'foo';
        $event->origin = 'node1';
        $this->assertFalse($chain->isEventSignedByIdentityNode($event, 'node1'));

        // not the same node
        $event->signkey = '4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG';
        $event->origin = 'node2';
        $this->assertFalse($chain->isEventSignedByIdentityNode($event, 'node1'));
        
        // same node and identity
        $event->signkey = '4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG';
        $event->origin = 'node2';
        $this->assertTrue($chain->isEventSignedByIdentityNode($event, 'node2'));
    }
}
