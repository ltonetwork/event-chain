<?php

/**
 * @covers ResourceManager
 */
class ResourceManagerTest extends \Codeception\Test\Unit
{
    public function testExtractFrom()
    {
        $manager = new ResourceManager([
            'http://example.com/' => ''
        ]);
    }
}
