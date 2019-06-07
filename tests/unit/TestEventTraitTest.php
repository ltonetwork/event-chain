<?php

use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;

/**
 * Test helper methods from TestEventTrait
 *
 * @covers TestEventTrait
 */
class TestEventTraitTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * Test 'castChainToData' method
     */
    public function testCastChainToData()
    {
        $event1 = new Event();
        $event1->origin = 'localhost';
        $event1->body = 'BFragPoLCHrFeo7BPi';
        $event1->timestamp = 1553350499;
        $event1->previous = '5XHFX34L1frZ6HNziXgKYLULUawQUgJtBphERx5TAGQM';
        $event1->signkey = 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y';
        $event1->signature = '5QBx38XZmTa2eLmzRoGRD8UEhboBUyFVU8Nh4CuZfha5HbBBupD4zY73T4ybfHiNS8SekAYkfjWYurBLvYTvDzUo';
        $event1->hash = 'CWt5xeswX5kn7CA6EZroowfHaQ1LEoWyuHpQz3Arp4ov';

        $event2 = new Event();
        $event2->origin = 'localhost';
        $event2->body = 'BFragPoLCHrFeo7BPi';
        $event2->timestamp = 1553350499;
        $event2->previous = 'CWt5xeswX5kn7CA6EZroowfHaQ1LEoWyuHpQz3Arp4ov';
        $event2->signkey = 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y';
        $event2->signature = 'J9G33YHL8KrMba5zk9euuYgSZzTFsNyfCePg8HQvrAtESx7zJJqJb81svUQsFKDndAbnoepAUVby7MD3XhwB4Aa';
        $event2->hash = 'HhrFfJMbmSEx8KE6PPQZeEj2yiPBCNichZeam6XQts6F';

        $identity = new Identity();
        $identity->id = 'd7e3935a-8d0e-4b14-b910-19df0bf5bbe8';
        $identity->signkeys = ['default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'];
        $identity->encryptkey = 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6';
        $identity->schema = 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#';

        $chain = new EventChain();
        $chain->id = '2c8H7PxSuXvmmUnMkfm7Guwd58vEDAm33dk7gFEKjsF7qbMFVMMzE81nNXB3hw';
        $chain->events = new EntitySet([$event1, $event2]);
        $chain->identities = new IdentitySet([$identity]);

        $data = $this->castChainToData($chain);

        $this->assertSame($chain->id, $data['id']);
        $this->assertCount(2, $data['events']);
        $this->assertCount(1, $data['identities']);
        $this->assertTrue(is_array($data['events']));
        $this->assertTrue(is_array($data['identities']));

        $fields = ['origin', 'body', 'timestamp', 'previous', 'signkey', 'signature', 'hash'];
        foreach ($fields as $field) {
            $this->assertSame($event1->$field, $data['events'][0][$field]);
            $this->assertSame($event2->$field, $data['events'][1][$field]);
        }

        $fields = ['id', 'node', 'encryptkey'];
        foreach ($fields as $field) {
            $this->assertSame($identity->$field, $data['identities'][0][$field]);
        }

        $this->assertSame($identity->schema, $data['identities'][0]['$schema']);
        $this->assertEquals($identity->signkeys, $data['identities'][0]['signkeys']);
    }

    /**
     * Test 'createEventChain' method with no events
     */
    public function testCreateEventChainEmpty()
    {
        $chain = $this->createEventChain(0);

        $this->assertInstanceOf(EventChain::class, $chain); 
        $this->assertNotEmpty($chain->id);
        $this->assertFalse($chain->hasEvents());
    }

    /**
     * Test 'createEventChain' method
     */
    public function testCreateEventChain()
    {
        $count = 3;

        $chain1 = $this->createEventChain($count);
        $chain2 = $this->createEventChain($count);

        $this->assertInstanceOf(EventChain::class, $chain1); 
        $this->assertInstanceOf(EventChain::class, $chain2); 
        $this->assertTrue($chain1->isValidId());
        $this->assertTrue($chain2->isValidId());
        $this->assertNotEquals($chain1->id, $chain2->id);

        $this->assertInstanceOf(EntitySet::class, $chain1->events);
        $this->assertInstanceOf(EntitySet::class, $chain2->events);
        $this->assertCount($count, $chain1->events);
        $this->assertCount($count, $chain2->events);
        $this->assertNotEquals($chain1->events[0], $chain2->events[0]);
        $this->assertNotEquals($chain1->events[1], $chain2->events[1]);
        $this->assertNotEquals($chain1->events[2], $chain2->events[2]);

        $validation1 = $chain1->validate();
        $validation2 = $chain1->validate();
        $this->assertTrue($validation1->succeeded());
        $this->assertTrue($validation2->succeeded());

        $node = App::getContainer()->get('node.account');

        for ($i=0; $i < $count; $i++) { 
            $this->assertTrue($chain1->events[$i]->verifySignature());
            $this->assertTrue($chain2->events[$i]->verifySignature());

            $this->assertTrue($chain1->isEventSignedByAccount($chain1->events[$i], $node));
            $this->assertTrue($chain2->isEventSignedByAccount($chain2->events[$i], $node));

            $this->assertNotEmpty($chain1->events[$i]->timestamp);
            $this->assertNotEmpty($chain2->events[$i]->timestamp);
            $this->assertNotEmpty($chain1->events[$i]->body);
            $this->assertNotEmpty($chain2->events[$i]->body);
        }
    }

    /**
     * Test 'createEventChain' method, if setting events' bodies
     */
    public function testCreateEventChainBodies()
    {
        $bodies = [
            ['boo1' => 'Test body 1'],
            ['boo2' => 'Test body 2'],
            ['boo3' => 'Test body 3']
        ];

        $chain = $this->createEventChain(3, $bodies);

        $this->assertCount(3, $chain->events);
        $this->assertEquals((object)['boo1' => 'Test body 1'], json_decode(base58_decode($chain->events[0]->body)));
        $this->assertEquals((object)['boo2' => 'Test body 2'], json_decode(base58_decode($chain->events[1]->body)));
        $this->assertEquals((object)['boo3' => 'Test body 3'], json_decode(base58_decode($chain->events[2]->body)));
    }

    /**
     * Test 'createEventChain' method, if setting events' bodies rises exception
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No body for test event [2]
     */
    public function testCreateEventChainBodiesException()
    {
        $bodies = [
            ['boo1' => 'Test body 1'],
            ['boo2' => 'Test body 2']
        ];

        $this->createEventChain(3, $bodies);
    }

    /**
     * Test 'createFork' method
     */
    public function testCreateFork()
    {
        $chain = $this->createEventChain(5);
        $fork = $this->createFork($chain, 2, 4);

        $this->assertInstanceOf(EventChain::class, $fork);
        $this->assertSame($chain->id, $fork->id);

        $this->assertCount(5, $chain->events);
        $this->assertCount(6, $fork->events);

        $this->assertSame($chain->events[0], $fork->events[0]);
        $this->assertSame($chain->events[1], $fork->events[1]);
        $this->assertNotEquals($chain->events[2], $fork->events[2]);
        $this->assertNotEquals($chain->events[3], $fork->events[3]);
        $this->assertNotEquals($chain->events[4], $fork->events[4]);

        $validation = $fork->validate();
        $this->assertTrue($validation->succeeded());

        $this->assertFalse($fork->isPartial());
    }

    /**
     * Test 'createPartialChain' method
     */
    public function testCreatePartialChain()
    {
        $chain = $this->createEventChain(5);
        $partial = $this->createPartialChain($chain, 3);

        $this->assertInstanceOf(EventChain::class, $partial);
        $this->assertSame($chain->id, $partial->id);

        $this->assertCount(5, $chain->events);
        $this->assertCount(3, $partial->events);
        $this->assertSame($chain->events[2], $partial->events[0]);
        $this->assertSame($chain->events[3], $partial->events[1]);
        $this->assertSame($chain->events[4], $partial->events[2]);

        $this->assertTrue($partial->isPartial());
    }

    /**
     * Test 'mapChains' method, if keys chain is shorter then values chain
     */
    public function testMapChainsKeysShorter()
    {
        $chainKeys = $this->createEventChain(2);
        $chainValues = $this->createEventChain(4);

        $pipe = $this->mapChains($chainKeys, $chainValues);

        $this->assertInstanceOf(Pipeline::class, $pipe);

        $keys = [];
        $values = [];
        foreach ($pipe as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $this->assertCount(4, $keys);
        $this->assertCount(4, $values);

        $this->assertSame($chainKeys->events[0], $keys[0]);
        $this->assertSame($chainKeys->events[1], $keys[1]);
        $this->assertSame(null, $keys[2]);
        $this->assertSame(null, $keys[3]);

        $this->assertSame($chainValues->events[0], $values[0]);
        $this->assertSame($chainValues->events[1], $values[1]);
        $this->assertSame($chainValues->events[2], $values[2]);
        $this->assertSame($chainValues->events[3], $values[3]);
    }

    /**
     * Test 'mapChains' method, if keys chain is longer then values chain
     */
    public function testMapChainsValuesShorter()
    {
        $chainKeys = $this->createEventChain(4);
        $chainValues = $this->createEventChain(2);

        $pipe = $this->mapChains($chainKeys, $chainValues);

        $this->assertInstanceOf(Pipeline::class, $pipe);

        $keys = [];
        $values = [];
        foreach ($pipe as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $this->assertCount(4, $keys);
        $this->assertCount(4, $values);

        $this->assertSame($chainKeys->events[0], $keys[0]);
        $this->assertSame($chainKeys->events[1], $keys[1]);
        $this->assertSame($chainKeys->events[2], $keys[2]);
        $this->assertSame($chainKeys->events[3], $keys[3]);

        $this->assertSame($chainValues->events[0], $values[0]);
        $this->assertSame($chainValues->events[1], $values[1]);
        $this->assertSame(null, $values[2]);
        $this->assertSame(null, $values[3]);
    }

    /**
     * Test 'mapChains' method, if keys chain is equal in length to values chain
     */
    public function testMapChains()
    {
        $chainKeys = $this->createEventChain(3);
        $chainValues = $this->createEventChain(3);

        $pipe = $this->mapChains($chainKeys, $chainValues);

        $this->assertInstanceOf(Pipeline::class, $pipe);

        $keys = [];
        $values = [];
        foreach ($pipe as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $this->assertCount(3, $keys);
        $this->assertCount(3, $values);

        $this->assertSame($chainKeys->events[0], $keys[0]);
        $this->assertSame($chainKeys->events[1], $keys[1]);
        $this->assertSame($chainKeys->events[2], $keys[2]);

        $this->assertSame($chainValues->events[0], $values[0]);
        $this->assertSame($chainValues->events[1], $values[1]);
        $this->assertSame($chainValues->events[2], $values[2]);
    }

    /**
     * Test 'addEvents' method
     */
    public function testAddEvents()
    {
        $chain = $this->createEventChain(2);
        $newChain = $this->addEvents($chain, 2);

        $this->assertSame($chain->id, $newChain->id);
        $this->assertCount(2, $chain->events);
        $this->assertCount(4, $newChain->events);

        $this->assertSame($chain->events[0], $newChain->events[0]);
        $this->assertSame($chain->events[1], $newChain->events[1]);
        $this->assertInstanceOf(Event::class, $newChain->events[2]);
        $this->assertInstanceOf(Event::class, $newChain->events[3]);

        $validation = $newChain->validate();
        $this->assertTrue($validation->succeeded());

        $node = App::getContainer()->get('node.account');

        for ($i = 2; $i < 4; $i++) { 
            $this->assertTrue($newChain->events[$i]->verifySignature());
            $this->assertTrue($newChain->isEventSignedByAccount($newChain->events[$i], $node));
            $this->assertNotEmpty($newChain->events[$i]->timestamp);
            $this->assertNotEmpty($newChain->events[$i]->body);
        }
    }

    /**
     * Test 'addEvents' method with custom events bodies
     */
    public function testAddEventsBodies()
    {
        $chain = $this->createEventChain(2);
        $newChain = $this->addEvents($chain, 2, [['foo'], ['bar']]);

        $this->assertSame($chain->id, $newChain->id);
        $this->assertCount(2, $chain->events);
        $this->assertCount(4, $newChain->events);

        $this->assertSame($chain->events[0], $newChain->events[0]);
        $this->assertSame($chain->events[1], $newChain->events[1]);
        $this->assertInstanceOf(Event::class, $newChain->events[2]);
        $this->assertInstanceOf(Event::class, $newChain->events[3]);

        $validation = $newChain->validate();
        $this->assertTrue($validation->succeeded());

        $node = App::getContainer()->get('node.account');

        for ($i = 2; $i < 4; $i++) { 
            $this->assertTrue($newChain->events[$i]->verifySignature());
            $this->assertTrue($newChain->isEventSignedByAccount($newChain->events[$i], $node));
            $this->assertNotEmpty($newChain->events[$i]->timestamp);
            $this->assertNotEmpty($newChain->events[$i]->body);
        }

        $body = $this->decodeEventBody($newChain->events[2]->body);
        $this->assertEquals(['foo'], $body);

        $body = $this->decodeEventBody($newChain->events[3]->body);
        $this->assertEquals(['bar'], $body);
    }

    /**
     * Test 'addEvents' method, if returned chain is partial
     */
    public function testAddEventsPartial()
    {
        $chain = $this->createEventChain(2);
        $newChain = $this->addEvents($chain, 3, [['foo'], ['bar'], ['baz']], true);

        $this->assertSame($chain->id, $newChain->id);
        $this->assertCount(2, $chain->events);
        $this->assertCount(3, $newChain->events);

        $this->assertNotSame($chain->events[0], $newChain->events[0]);
        $this->assertNotSame($chain->events[1], $newChain->events[1]);
        $this->assertInstanceOf(Event::class, $newChain->events[2]);

        $validation = $newChain->validate();
        $this->assertTrue($validation->succeeded());

        $node = App::getContainer()->get('node.account');

        for ($i = 0; $i < 3; $i++) { 
            $this->assertTrue($newChain->events[$i]->verifySignature());
            $this->assertTrue($newChain->isEventSignedByAccount($newChain->events[$i], $node));
            $this->assertNotEmpty($newChain->events[$i]->timestamp);
            $this->assertNotEmpty($newChain->events[$i]->body);
        }

        $body1 = $this->decodeEventBody($newChain->events[0]->body);
        $body2 = $this->decodeEventBody($newChain->events[1]->body);
        $body3 = $this->decodeEventBody($newChain->events[2]->body);

        $this->assertEquals(['foo'], $body1);
        $this->assertEquals(['bar'], $body2);
        $this->assertEquals(['baz'], $body3);
    }
}
