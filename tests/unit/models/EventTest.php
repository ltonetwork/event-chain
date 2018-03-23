<?php

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
        
        $keypair = sodium\crypto_sign_seed_keypair($seed);
        $publickey = sodium\crypto_sign_publickey($keypair);
        $secretkey = sodium\crypto_sign_secretkey($keypair);
        
        $base58 = new StephenHill\Base58();
        
        $this->assertEquals("8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj", $base58->encode($publickey));
        $this->assertEquals("3s8rY83hdyfn2pLQu3yH6DXtK9QGsjPw7TXKPRfTLCQeVR4gGkR7LDig5ABFEKFVyavr8zprpzqBFKhUEvfNaXGV",
                $base58->encode($secretkey));
    }
    
    public function testAddReceipt()
    {
        $receipt = $this->createMock(Receipt::class);

        $event = Event::create();
        
        $ret = $event->addReceipt($receipt);
        
        $this->assertSame($ret, $event);
        $this->assertSame($receipt, $event->receipt);
    }
    
    /**
     * Test getting a hash
     */
    public function testGetHash()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj"
        ]);
        
        $this->assertEquals('ArxW6PhABV2JUd7VeqfWGjVJ4hyXEhCztKRP1gJKLchH', $event->getHash());
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
    }
    
    public function testVerifySignature()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "signature" => "3S72dRFjpdnbrdBneRpBxzGb99eEE6X3wCnKC4GiN2MwE1i3Xx1zVtzFeeUVwq3qMTECn8HzEJPJZCgU2iEE7227"
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
            "timestamp" => new DateTime("2017-11-09T00:00:00+00:00"), // Back dated
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => $signkey,
            "signature" => $signature
        ]);
        
        $this->assertFalse($event->verifySignature());
    }
    
    public function testValidate()
    {
        $receipt = $this->createMock(Receipt::class);
        $receipt->expects($this->once())->method('validate')->willReturn(\Jasny\ValidationResult::success());
        $receipt->targetHash = "ArxW6PhABV2JUd7VeqfWGjVJ4hyXEhCztKRP1gJKLchH";
        
        $event = $this->createPartialMock(Event::class, ['verifySignature']);
        $event->expects($this->once())->method('verifySignature')->willReturn(true);
        
        $event->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "hash" => "ArxW6PhABV2JUd7VeqfWGjVJ4hyXEhCztKRP1gJKLchH",
            "signature" => "",
            "receipt" => $receipt
        ]);
        
        $validation = $event->validate();
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateFailed()
    {
        $receipt = $this->createMock(Receipt::class);
        $receipt->expects($this->once())->method('validate')
                ->willReturn(\Jasny\ValidationResult::error('some error'));
        $receipt->targetHash = "ArxW6PhABV2JUd7VeqfWGjVJ4hyXEhCztKRP1gJKLchH";
        
        $event = $this->createPartialMock(Event::class, ['verifySignature']);
        $event->expects($this->once())->method('verifySignature')->willReturn(false);
        
        $event->setValues([
            "body" => "abc",
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "8TxFbgGPKVhuauHJ47vn3C74eVugAghTGou35Wtd51Mj",
            "hash" => "EdqM52SpXCn5c1uozuvuH5o9Tcr41kYeCWz4Ymu6ngbt",
            "signature" => "",
            "receipt" => $receipt
        ]);

        
        $identity = $this->createMock(Identity::class);
        $identity->id = "73092191-6936-4d44-a942-02be14664ebb";
        $identity->signkeys['user'] = "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP";
        
        $previous = "GKot5hBsd81kMupNCXHaqbhv3huEbxAFMLnpcX2hniwn";
        
        $validation = $event->validate(compact('identity', 'previous'));
        
        $this->assertEquals([
            'body is not base58 encoded json',
            'invalid signature',
            'invalid hash',
            "invalid receipt; some error",
            "invalid receipt; hash doesn't match"
        ], $validation->getErrors());
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
}
