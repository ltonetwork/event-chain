<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{
    /**
     * Adds Signature authentication via ED25519 secret key.
     *
     * @param string $secretkey
     * @part json
     * @part xml
     */
    public function amSignatureAuthenticated($secretkey)
    {
        $module = $this->getModule(Jasny\Codeception\Module::class);
        
        $account = $module->get(\LTO\AccountFactory::class)->create($secretkey);
        $module->get('auth')->account = $account;
    }
    
    
    /**
     * Set responses for Guzzle mock
     * 
     * @param \GuzzleHttp\Psr7\Response $response
     * @param ...
     */
    public function responseToHttpRequestWith(...$responses)
    {
        $module = $this->getModule(Jasny\Codeception\Module::class);
        
        $mock = $module->container->get(GuzzleHttp\Handler\MockHandler::class);
        $mock->append(...$responses);
    }

    /**
     * Assert the number of http requests
     * 
     * @param int $count  Call number
     * @return \GuzzleHttp\Psr7\Request
     */
    public function seeNumHttpRequestWare($count)
    {
        $module = $this->getModule(Jasny\Codeception\Module::class);
        $history = $module->get('httpHistory');
        
        $this->assertEquals($count, count($history));
    }
    
    /**
     * Get a http trigger request from history
     * 
     * @param int $i  Call number
     * @return \GuzzleHttp\Psr7\Request
     */
    public function grabHttpRequest($i = -1)
    {
        $module = $this->getModule(Jasny\Codeception\Module::class);
        $history = $module->get('httpHistory');
        
        if ($i < 0) {
            $i = count($history) + $i;
        }
        
        return isset($history[$i]) ? $history[$i]['request'] : null;
    }
}
