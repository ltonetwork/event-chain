<?php

/**
 * @covers NoAnchorClient
 */
class NoAnchorClientTest extends \Codeception\Test\Unit
{
    /**
     * Provide data for testing throwing expcetion
     *
     * @return array
     */
    public function exceptionProvider()
    {
        return [
            ['fetch', ['foo']],
            ['fetchMultiple', [[]]]
        ];
    }

    /**
     * Test throwing exception
     *
     * @dataProvider exceptionProvider
     * @expectedException Exception
     * @expectedExceptionMessage Unable to fetch information from anchoring service. The event-chain service runs in a local-only setup (anchor disabled).
     */
    public function testException($method, $args)
    {
        $service = new NoAnchorClient();
        $service->$method(...$args);
    }
}
