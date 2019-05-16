<?php

/**
 * @covers Identity
 */
class IdentityTest extends \Codeception\Test\Unit
{
    /**
     * Test 'getIdProperty' method
     */
    public function testGetIdProperty()
    {
        $result = Identity::getIdProperty();

        $this->assertSame('id', $result);
    }

    /**
     * Test 'jsonSerialize' method
     */
    public function testJsonSerialize()
    {
        $identity = new Identity();
        $identity->timestamp = (new DateTime())->setTimestamp(1234);

        $result = $identity->jsonSerialize();

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame(1234, $result->timestamp);
    }
}
