<?php

use LTO\Account;
use function LTO\sha256;
use function sodium_crypto_generichash as blake2b;
use Jasny\DB\EntitySet;

/**
 * @covers EventChain
 */
class EventChainTest extends \Codeception\Test\Unit
{
    use TestEventTrait;
    use Jasny\TestHelper;

    /**
     * @coversNothing
     */
    public function testCreateId()
    {
        $signkey = base58_decode("FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y");

        $signkeyHashed = substr(sha256(blake2b($signkey)), 0, 20);
        $this->assertEquals("2tBDhkXEsuVw2bqVsAhwHdZysRaP", base58_encode($signkeyHashed));

        $nonce = str_repeat("\0", 20);

        $packed = pack('Ca20a20', \LTO\EventChain::CHAIN_ID, $nonce, $signkeyHashed);
        $chksum = substr(sha256(blake2b($packed)), 0, 4);
        $this->assertEquals("4fop85", base58_encode($chksum));

        $id = pack('Ca20a20a4', \LTO\EventChain::CHAIN_ID, $nonce, $signkeyHashed, $chksum);
        $this->assertEquals('2ar3wSjTm1fA33qgckZ5Kxn1x89gKTivEeXtSLPmAXbZp8zA8XgiFyJGPxv6hj', base58_encode($id));
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
        $event->previous = "DzyXf5T8kVXeYoH2sRhqbBLAYMsqV2iiz2sYQjViY6Py";
        $event->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        
        $chain = EventChain::create()->setValues([
            'id' => '2b5BRFSdLr8FCE2SPgMKergAAZ2Uy2785sxhsCxgGMcsyH92YKovJvEG4voXLg',
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
        $event1->previous = "8nrgSyPw6tCvqvVj8raVoBstPuGS2fsMr1KxHfGvnXSP";
        $event1->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        $event1->hash = "7vBps1mev7CjAC6zC5AiaETZunm8f18WDtoPCcqeDrJz";
        
        $event2 = $this->createMock(Event::class);
        $event2->previous = "7vBps1mev7CjAC6zC5AiaETZunm8f18WDtoPCcqeDrJz";
        $event2->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        $event2->hash = "DcLL5Ek4Tcpx7YNQC4nWCdtZLjukKHpso9PaPdR688xY";
        
        $chain = EventChain::create()->setValues([
            'id' =>  '2bXoH4bbtzeBr2m2Hvd36sz7K6hJkuaArj4UpmwzUqozsfGD2exGdRjLuWNRUA',
            'events' => [ $event1, $event2 ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateIntegrityFailed()
    {
        $event1 = $this->createMock(Event::class);
        $event1->previous = "8nrgSyPw6tCvqvVj8raVoBstPuGS2fsMr1KxHfGvnXSP";
        $event1->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        $event1->hash = "7vBps1mev7CjAC6zC5AiaETZunm8f18WDtoPCcqeDrJz";
        
        $event3 = $this->createMock(Event::class);
        $event3->previous = "J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS";
        $event3->signkey = "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y";
        $event3->hash = "DcLL5Ek4Tcpx7YNQC4nWCdtZLjukKHpso9PaPdR688xY";
        
        $chain = EventChain::create()->setValues([
            'id' =>  '2bXoH4bbtzeBr2m2Hvd36sz7K6hJkuaArj4UpmwzUqozsfGD2exGdRjLuWNRUA',
            'events' => [ $event1, $event3 ]
        ]);
        
        $validation = $chain->validate();
        
        $this->assertEquals([
            "broken chain; previous of 'DcLL5Ek4Tcpx7YNQC4nWCdtZLjukKHpso9PaPdR688xY' is 'J26EAStUDXdRUMhm1UcYXUKtJWTkcZsFpxHRzhkStzbS', expected '7vBps1mev7CjAC6zC5AiaETZunm8f18WDtoPCcqeDrJz'"
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
        $resource->expects($this->once())->method('getId')->willReturn('lt:/foos/123');
        
        $chain = EventChain::create();
        $chain->identities = $this->createMock(IdentitySet::class);
        $chain->identities->expects($this->never())->method('set');
        
        $chain->registerResource($resource);
        
        $this->assertEquals(['lt:/foos/123'], $chain->resources);
    }
    
    public function testRegisterResourceExisting()
    {
        $resource = $this->createMock(ExternalResource::class);
        $resource->expects($this->once())->method('getId')->willReturn('lt:/foos/123');
        
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

    public function testHasSystemKeyForIdentity()
    {
        $identities = [
            [
                "id" => "1",
                "signkeys" => [
                    "default" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ",
                    "system" => "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"
                ]
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "default" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG",
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
                    "default" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ",
                    "system" => "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"
                ]
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "default" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG",
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
                    "default" => "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ"
                ],
                'node' => 'node1'
            ],
            [
                "id" => "2",
                "signkeys" => [
                    "default" => "4WfbPKDYJmuZeJUHgwnVV64mBeMqMbSGt1p75UegcSCG"
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

    /**
     * Provide data for testing 'isValidId' method
     *
     * @return array
     */
    public function isValidIdProvider()
    {
        $chain1 = $this->createEventChain(1);
        $chain2 = $this->createEventChain(1);
        $chain2->id = base58_encode('foo');

        return [
            [$chain1, true],
            [$chain2, false]
        ];
    }

    /**
     * Test 'isValidId' method
     *
     * @dataProvider isValidIdProvider
     */
    public function testIsValidId($chain, $expected)
    {
        $result = $chain->isValidId();

        $this->assertSame($expected, $result);
    }

    /**
     * Test 'isValidId' method, if chain has no events
     *
     * @expectedException UnderflowException
     * @expectedExceptionMessage chain has no events
     */
    public function testIsValidIdNoEvents()
    {
        $chain = $this->createEventChain(0);

        $chain->isValidId();
    }

    /**
     * Test 'withEvents' method
     */
    public function testWithEvents()
    {
        $identities = [
            $this->createMock(Identity::class),
            $this->createMock(Identity::class)
        ];

        $chain = $this->createEventChain(3);
        $chain->resources = ['foo', 'bar'];
        $chain->identities = $identities;

        $events = [
            $this->createMock(Event::class),
            $this->createMock(Event::class)
        ];

        $result = $chain->withEvents($events);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertSame($chain->id, $result->id);
        $this->assertEquals(['foo', 'bar'], $result->resources);
        $this->assertEquals($identities, $result->identities);

        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertCount(2, $result->events);
        $this->assertSame($events[0], $result->events[0]);
        $this->assertSame($events[1], $result->events[1]);
    }

    /**
     * Test 'getPartialAfter' method
     */
    public function testGetPartialAfter()
    {
        $chain = $this->createEventChain(5);
        $identities = [
            $this->createMock(Identity::class),
            $this->createMock(Identity::class)
        ];

        $chain->identities = $identities;
        $chain->resources = ['foo', 'bar'];

        $prevEvents = $chain->events;
        $hash = $chain->events[2]->hash;

        $result = $chain->getPartialAfter($hash);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertSame($chain->id, $result->id);
        $this->assertEquals(['foo', 'bar'], $result->resources);
        $this->assertEquals($identities, $result->identities);

        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertCount(2, $result->events);
        $this->assertSame($prevEvents[3], $result->events[0]);
        $this->assertSame($prevEvents[4], $result->events[1]);
    }

    /**
     * Test 'getPartialAfter' method for initial hash
     */
    public function testGetPartialAfterInitialHash()
    {
        $chain = $this->createEventChain(5);
        $identities = [
            $this->createMock(Identity::class),
            $this->createMock(Identity::class)
        ];

        $chain->identities = $identities;
        $chain->resources = ['foo', 'bar'];

        $prevEvents = $chain->events;
        $hash = $chain->getInitialHash();

        $result = $chain->getPartialAfter($hash);

        $this->assertInstanceOf(EventChain::class, $result);
        $this->assertSame($chain->id, $result->id);
        $this->assertEquals(['foo', 'bar'], $result->resources);
        $this->assertEquals($identities, $result->identities);

        $this->assertInstanceOf(EntitySet::class, $result->events);
        $this->assertEquals($prevEvents, $result->events);
    }

    /**
     * Test 'getPartialAfter' method for not-existing hash
     *
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Event 'foo' not found
     */
    public function testGetPartialAfterNotExistHash()
    {
        $chain = $this->createEventChain(2);

        $chain->getPartialAfter('foo');
    }

    /**
     * Test '__clone' method
     */
    public function testClone()
    {
        $chain = $this->createEventChain(2);
        $events = $chain->events;

        $result = clone $chain;

        $this->assertEquals($events, $result->events);
        $this->assertNotSame($events, $result->events);
    }

    /**
     * Test 'filterToQuery' method
     */
    public function testFilterToQuery()
    {
        $filter = ['chains_for' => 'foo'];
        $chain = $this->createEventChain(1);

        $result = $this->callPrivateMethod($chain, 'filterToQuery', [$filter]);

        $expected = [
            '$or' => [
                ['identities.signkeys.default' => 'foo'],
                ['identities.signkeys.system' => 'foo']
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
