<?php
namespace Helper;

use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use MongoDB\Model\ObjectId as MongoId;
use MongoDB\BSON\UTCDateTime;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{
    /**
     * @return \Codeception\Module
     */
    public function getJasnyModule()
    {
        return $this->getModule('\Jasny\Codeception\Module');
    }
    
    /**
     * Adds Signature authentication via ED25519 secret key.
     *
     * @param string $secretkey
     * @part json
     * @part xml
     */
    public function amSignatureAuthenticated($secretkey)
    {
        $module = $this->getJasnyModule();
        
        $accountFactory = $module->container->get(\LTO\AccountFactory::class);
        $account = $accountFactory->create($secretkey, 'base64');
        
        $request = $module->client->getBaseRequest()->withAttribute('account', $account);
        $module->client->setBaseRequest($request);
    }
    
    /**
     * Removes Signature authentication.
     *
     * @part json
     * @part xml
     */
    public function amNotSignatureAuthenticated()
    {
        $module = $this->getJasnyModule();
        $module->client->setBaseRequest($module->client->getBaseRequest()->withAttribute('account', null));
    }
    
    /**
     * Set responses for Guzzle mock
     * 
     * @param callable|\GuzzleHttp\Psr7\Response $response
     * @param ...
     */
    public function expectHttpRequest($response)
    {
        $module = $this->getJasnyModule();
        
        $mock = $module->container->get(\GuzzleHttp\Handler\MockHandler::class);
        $mock->append($response);
    }

    /**
     * Assert the number of http requests
     * 
     * @param int $count  Call number
     * @return \GuzzleHttp\Psr7\Request
     */
    public function seeNumHttpRequestWare($count)
    {
        $module = $this->getJasnyModule();
        $history = $module->container->get('httpHistory');
        
        $message = "Expected $count HTTP requests";
        
        \PHPUnit\Framework\Assert::assertCount($count, $history, $message);
    }
    
    /**
     * Get a http trigger request from history
     * 
     * @param int $i  Call number
     * @return \GuzzleHttp\Psr7\Request
     */
    public function grabHttpRequest($i = -1)
    {
        $module = $this->getJasnyModule();
        $history = $module->container->get('httpHistory');
        
        if ($i < 0) {
            $i = count($history) + $i;
        }
        
        return isset($history[$i]) ? $history[$i]['request'] : null;
    }
    
    /**
     * 
     * @param type $actualJson
     * @param type $message
     */
    public function assertJson($actualJson, $message = '')
    {
        \PHPUnit\Framework\Assert::assertJson($actualJson, $message);
    }        
    
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     * 
     * @param string $expectedJson
     * @param string $actualJson
     * @param string $message
     */
    public function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        \PHPUnit\Framework\Assert::assertJson($expectedJson, $message);
        \PHPUnit\Framework\Assert::assertJson($actualJson, $message);

        $expected = json_decode($expectedJson, true);
        $actual   = json_decode($actualJson, true);

        \PHPUnit\Framework\Assert::assertEquals($expected, $actual, $message);
    }
    
    
    /**
     * Asserts that a variable is equal to an attribute of an object.
     * 
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        \PHPUnit\Framework\Assert::assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject,
            $message);
    }
    
    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains a needle.
     * 
     * @param mixed         $needle
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     */
    public function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        \PHPUnit\Framework\Assert::assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject,
            $message);
    }
    
    /**
     * Cast MongoDB objects
     * 
     * @param mixed $document
     * @return mixed
     */
    protected function castMongoDocument($document)
    {
        if ($document instanceof BSONArray || $document instanceof BSONDocument) {
            $document = $document->getArrayCopy();
        } elseif ($document instanceof MongoId) {
            $document = (string)$document;
        } elseif ($document instanceof UTCDateTime) {
            $document = $document->toDateTime()->format('c');
        }
        
        if (is_array($document) || is_object($document)) {
            foreach ($document as &$value) {
                $value = $this->castMongoDocument($value);
            }
        }
        
        return $document;
    }
    
    /**
     * Asserts that mongo document equals variables.
     *
     * @param mixed  $expected
     * @param mixed  $actualDocument
     * @param string $message
     */
    public function assertMongoDocumentEquals($expected, $actualDocument, $message = '')
    {
        $actual = $this->castMongoDocument($actualDocument);
        
        \PHPUnit\Framework\Assert::assertEquals($expected, $actual, $message);
    }
}
