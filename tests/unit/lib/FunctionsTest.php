<?php

use Jasny\DB\EntitySet;

class FunctionsTest extends \Codeception\Test\Unit
{
    /**
     * Provide data for testing 'is_schema_link_valid' function
     *
     * @return array
     */
    public function isSchemaLinkValidProvider()
    {
        return [
            ['foo', 'foo', false],
            ['https://specs.livecontracts.io/0.2.0/identity/schema.json#', 'identity', false],
            ['https://foo/v0.2.0/identity/schema.json#', 'identity', false],
            ['https://specs.livecontracts.io/identity/schema.json#', 'identity', false],
            ['http://specs.livecontracts.io/v0.2.0/identity/schema.json#', 'identity', false],
            ['specs.livecontracts.io/v0.2.0/identity/schema.json#', 'identity', false],
            ['https://specs.livecontracts.io/v0.2.0/identity/schema.json#', 'process', false],
            ['https://specs.livecontracts.io/v0.2.0a/identity/schema.json#', 'identity', false],
            ['https://specs.livecontracts.io/v0.2.0/identity/schema.json#', 'identity', true],
            ['https://specs.livecontracts.io/v10.465.3/identity/schema.json#', 'identity', true],
        ];
    }

    /**
     * Test 'is_schema_link_valid' function
     *
     * @dataProvider isSchemaLinkValidProvider
     */
    public function testIsSchemaLinkValid($schema, $type, $expected)
    {
        $result = is_schema_link_valid($schema, $type);

        $this->assertSame($expected, $result);
    }
}
