<?php

use LTO\Account;

/**
 * @covers Event
 */
class EventTest extends \Codeception\Test\Unit
{
    /**
     * @coversNothing
     */
    public function testCreateKeyPair()
    {
        $seed = hash('sha256', "a seed", true);
        
        $keypair = sodium_crypto_sign_seed_keypair($seed);
        $publickey = sodium_crypto_sign_publickey($keypair);
        $secretkey = sodium_crypto_sign_secretkey($keypair);
        
        $base58 = new StephenHill\Base58();
        
        $this->assertEquals("8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj", $base58->encode($publickey));
        $this->assertEquals("3s8rY83hdyfn2pLQu3yH6DXtK9QGsjPw7TXKPRfTLCQeVR4gGkR7LDig5ABFEKFVyavr8zprpzqBFKhUEvfNaXGV",
                $base58->encode($secretkey));
    }

    public function testGetMessage()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj"
        ]);
        
        $expected = join("\n", [
            "A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE",
            "1514764800",
            "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj"
        ]);
        
        $this->assertEquals($expected, $event->getMessage());
    }
   
    public function testGetHash()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj"
        ]);
        
        $this->assertEquals('H8qGksJvpAS77cjoTDfmabuob4KHtQCQeqS5s915WQmd', $event->getHash());
    }
    
    public function testGetBody()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE'
        ]);
        
        $expected = [
            "foo" => "bar",
            "good" => 1,
            "color" => "red"
        ];
        
        $this->assertEquals($expected, $event->getBody());
        
        // Second time from cache
        $event->body = "";
        $this->assertEquals($expected, $event->getBody());        
    }
    
    public function testVerifySignature()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "signature" => "3pkDcJ9gvT5iXy5F9DkVgv79nPrq8r24EK7ih1ibKszyohn6sgBJx8E5mpCXkm9HyUJjhV1dspUW6mrpuMj5CQjK"
        ]);
        
        $this->assertTrue($event->verifySignature());
    }
    
    public function verifySignatureFailProvider()
    {
        return [
            [
                "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
                "5rFR7MV6S7vjwMMsGGKVZ3im57jfsFxrCT3HyoFbuxjTi86JFBQBLupWdPXfgVGTf1ZeL74LP2fxZbr2Czb8MnvT"
            ],
            [
                "DptF5xtqwsPVLuSdEgMrVmQ2pYPjmCuwudYE5e23vGTT",
                "3S72dRFjpdnbrdBneRpBxzGb99eEE6X3wCnKC4GiN2MwE1i3Xx1zVtzFeeUVwq3qMTECn8HzEJPJZCgU2iEE7227"
            ],
            [ "abcd", "3S72dRFjpdnbrdBneRpBxzGb99eEE6X3wCnKC4GiN2MwE1i3Xx1zVtzFeeUVwq3qMTECn8HzEJPJZCgU2iEE7227" ],
            [ "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj", "abcd" ],
            [ "", "3S72dRFjpdnbrdBneRpBxzGb99eEE6X3wCnKC4GiN2MwE1i3Xx1zVtzFeeUVwq3qMTECn8HzEJPJZCgU2iEE7227" ],
            [ "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj", "" ],
            [ null, "3S72dRFjpdnbrdBneRpBxzGb99eEE6X3wCnKC4GiN2MwE1i3Xx1zVtzFeeUVwq3qMTECn8HzEJPJZCgU2iEE7227" ],
            [ "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj", null ],
            [ null, null ],
            [ "", "" ]
        ];
    }
    
    /**
     * @dataProvider verifySignatureFailProvider
     * 
     * @param string $signkey
     * @param string $signature
     */
    public function testVerifySignatureFail($signkey, $signature)
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => (new DateTime("2017-11-09T00:00:00+00:00"))->getTimestamp(), // Back dated
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => $signkey,
            "signature" => $signature
        ]);
        
        $this->assertFalse($event->verifySignature());
    }
    
    public function testValidate()
    {        
        $event = $this->createPartialMock(Event::class, ['verifySignature']);
        $event->expects($this->once())->method('verifySignature')->willReturn(true);
        
        $event->setValues([
            "body" => 'pvabUdSJtsf1ftYbgmNjUrMbnScRk2fJhGR3jk9t8td9xPJJzLqNFm8pr6ZpA7UQv1CVSHjKuarH8cNCDc524gh1WU',
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "hash" => "H3gbBd2sUczYCEqPK6LUPvVLqKqHdRNFEaaqAQe83mRQ",
            "signature" => "_stub_",
            "origin" => "node1"
        ]);
 
        $validation = $event->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateFailed()
    {        
        $event = $this->createPartialMock(Event::class, ['verifySignature']);
        $event->expects($this->once())->method('verifySignature')->willReturn(false);
        
        $event->setValues([
            "body" => "abc",
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "hash" => "EdqM52SpXCn5c1uozuvuH5o9Tcr41kYeCWz4Ymu6ngbt",
            "signature" => "",
            "origin" => "node1"
        ]);
        
        $identity = $this->createMock(Identity::class);
        $identity->id = "73092191-6936-4d44-a942-02be14664ebb";
        $identity->signkeys['user'] = "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP";
        
        $previous = "GKot5hBsd81kMupNCXHaqbhv3huEbxAFMLnpcX2hniwn";
        
        $validation = $event->validate(compact('identity', 'previous'));
        
        $this->assertEquals([
            'body is not base58 encoded json',
            'invalid signature',
            'invalid hash'
        ], $validation->getErrors());
    }
    
    public function testValidateNoSchema()
    {
        $event = $this->createPartialMock(Event::class, ['verifySignature']);
        $event->expects($this->once())->method('verifySignature')->willReturn(true);
        
        $event->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => (new DateTime("2018-01-01T00:00:00+00:00"))->getTimestamp(),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "hash" => "H8qGksJvpAS77cjoTDfmabuob4KHtQCQeqS5s915WQmd",
            "signature" => "_stub_",
            "origin" => "node1"
        ]);
        
        $validation = $event->validate();
        
        $this->assertEquals(['body is does not contain the $schema property'], $validation->getErrors());
    }
    
    public function testValidateRequired()
    {
        $event = Event::create();
        
        $validation = $event->validate();
        
        $this->assertEquals([
            'body is required',
            'timestamp is required',
            'previous is required',
            'signkey is required',
            'signature is required',
            'hash is required'
        ], $validation->getErrors());
    }
    
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Event is immutable
     */
    public function testSetValuesImmutable()
    {
        $event = $this->createPartialMock(Event::class, ['isNew']);
        $event->expects($this->once())->method('isNew')->willReturn(false);
        
        $event->setValues([]);
    }

    /**
     * Test 'signWith' method
     */
    public function testSignWith()
    {
        $event = $this->createPartialMock(Event::class, ['castToLtoEvent', 'setValues']);
        $ltoEvent = $this->createMock(LTO\Event::class);
        $node = $this->createMock(Account::class);

        $ltoEvent->signkey = 'foo';
        $ltoEvent->signature = 'bar';
        $ltoEvent->hash = 'baz';

        $event->expects($this->once())->method('castToLtoEvent')->willReturn($ltoEvent);
        $node->expects($this->once())->method('signEvent')->with($ltoEvent)->willReturn($ltoEvent);
        $event->expects($this->once())->method('setValues')->with([
            'signkey' => 'foo',
            'signature' => 'bar',
            'hash' => 'baz'
        ]);

        $result = $event->signWith($node);
        $this->assertSame($event, $result);
    }

    /**
     * Test 'castToLtoEvent' method
     */
    public function testCastToLtoEvent()
    {
        $event = new Event();
        $event->body = 'foo';

        $result = $event->castToLtoEvent();

        $this->assertInstanceOf(LTO\Event::class, $result);
        $this->assertSame('foo', $result->body);
    }
}
