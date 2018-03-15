<?php

/**
 * @covers MongoDocument
 */
class MongoDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provide data for testing 'isValidMongoId' method
     * 
     * @return array
     */
    public function isValidMongoIdProvider()
    {
        return [
            ['5955e63d9ed9ba047c736b0e', true],
            [new MongoId(), true],
            ['123456', false],
            ['some id', false],
            ['5955e63d9ed9ba047c736b0!', false]
        ];
    }

    /**
     * Test 'isValidMongoId' method
     *
     * @dataProvider isValidMongoIdProvider
     * @param string|MongoId $id 
     * @param boolean $result 
     */
    public function testIsValidMongoId($id, $result)
    {
        $this->assertEquals($result, MongoDocument::isValidMongoId($id));
    }
}
