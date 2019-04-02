<?php

/**
 * @covers NoDispatcher
 */
class NoDispatcherTest extends \Codeception\Test\Unit
{
    /**
     * Provide data for testing throwing expcetion
     *
     * @return array
     */
    public function exceptionProvider()
    {
        return [
            ['info'],
            ['getNode'],
            ['queue']
        ];
    }

    /**
     * Test throwing exception
     *
     * @dataProvider exceptionProvider
     * @expectedException Exception
     * @expectedExceptionMessage Unable to dispatch events to 'node1'. The event-chain service runs in a local-only setup (queuer disabled). Make sure all identities are using system key 'YOUR KEY HERE'
     */
    public function testException($method)
    {
        $service = new NoDispatcher();
        $chain = $this->createMock(EventChain::class);

        $method === 'queue' ?
            $service->$method($chain) :
            $service->$method();
    }
}
