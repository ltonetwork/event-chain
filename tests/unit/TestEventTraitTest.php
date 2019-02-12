<?php

use Jasny\DB\EntitySet;
use Improved\IteratorPipeline\Pipeline;

/**
 * Test helper methods from TestEventTrait
 */
class TestEventTraitTest extends \Codeception\Test\Unit
{
    use TestEventTrait;

    /**
     * Test 'createEventChain' method with no events
     */
    public function testCreateEventChainEmpty()
    {
        $chain = $this->createEventChain(0);

        $this->assertInstanceOf(EventChain::class, $chain); 
        $this->assertNotEmpty($chain->id);
        $this->assertTrue($chain->isEmpty());
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
}
