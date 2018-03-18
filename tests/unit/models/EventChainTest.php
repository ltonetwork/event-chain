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

    public function testValidateId()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->once())->method('validate')->willReturn(Jasny\ValidationResult::success());
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $eventChain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ $event ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }

    public function testValidateNowEvents()
    {
        $eventChain = EventChain::create()->setValues([
            'id' => 'JEKNVnkbo3jqSHT8tfiAKK4tQTFK7jbx8t18wEEnygya',
            'events' => [ ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals(['no events'], $validation->getErrors());
    }
    
    public function testValidateIdFail()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->once())->method('validate')->willReturn(Jasny\ValidationResult::success());
        $event->signkey = "8MeRTc26xZqPmQ3Q29RJBwtgtXDPwR7P9QNArymjPLVQ";
        
        $eventChain = EventChain::create()->setValues([
            'id' => '2JkYmWa9gyT32xT2gWvkGbLHXziw6Qy517KzEvUttigtmM',
            'events' => [ $event ]
        ]);
        
        $validation = $eventChain->validate();
        
        $this->assertEquals(['invalid id'], $validation->getErrors());
    }
}
