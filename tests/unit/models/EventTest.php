<?php

/**
 * @internal private key: FYrAcgvAgxBGL3gvpbecySCowCg3hfPP3rS6U6qXffBs
 * 
 * @covers Event
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getting a hash
     */
    public function testGetHash()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP"
        ]);
        
        $this->assertEquals('2kTjNfrftKdG5iPv743SdJgToxqqUQ5j7C3YU4pUraDm', $event->getHash());
    }
    
    public function testGetBody()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE'
        ]);
        
        $expected = (object)[
            "foo" => "bar",
            "good" => 1,
            "color" => "red"
        ];
        
        $this->assertEquals($expected, $event->getBody());
    }
    
    public function testVerifySignature()
    {
        $this->markTestSkipped("verify signature not implemented");
        
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP",
            "signature" => ""
        ]);
        
        $this->assertTrue($event->verifySignature());
    }
    
    public function testVerifySignatureFail()
    {
        $this->markTestSkipped("verify signature not implemented");
        
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2017-11-09T00:00:00+00:00"), // Back dated
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP",
            "signature" => ""
        ]);
        
        $this->assertFalse($event->verifySignature());
    }
    
    public function testValidateSuccess()
    {
        $event = Event::create()->setValues([
            "body" => 'A54BREAPQiWqZo3k9RQJ1U4yZBjyDj37aciJMiAJfNACHVoZVDYi3Q2qhqE',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP",
            "signature" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP",
            "hash" => "2kTjNfrftKdG5iPv743SdJgToxqqUQ5j7C3YU4pUraDm"
        ]);
        
        $previous = "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW";
        
        $validation = $event->validate(compact('identity', 'previous'));
        
        $this->assertEquals([], $validation->getErrors());
    }
    
    public function testValidateFailed()
    {
        $event = Event::create()->setValues([
            "body" => 'abc',
            "timestamp" => new DateTime("2018-01-01T00:00:00+00:00"),
            "previous" => "72gRWx4C1Egqz9xvUBCYVdgh7uLc5kmGbjXFhiknNCTW",
            "signkey" => "Cd5ZmfWYjuKVLVZA7YszxiGWdpVewQWTWurYDpWejohP",
            "signature" => "",
            "hash" => "EdqM52SpXCn5c1uozuvuH5o9Tcr41kYeCWz4Ymu6ngbt"
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
            "event does not fit chain after $previous"
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
