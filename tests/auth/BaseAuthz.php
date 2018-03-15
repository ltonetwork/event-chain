<?php

class BaseAuthz
{
    /**
     * Test going to given url
     *
     * @param string $url
     */
    protected function testUrl($url, FunctionalTester $I, \Codeception\Example $example, $isPost = false)
    {
        if ($example['email']) {
            $I->amLoggedInAs($example['email']);
        }

        $isPost ?
            $I->sendPost($url, isset($example['data']) ? $example['data'] : []) :
            $I->amOnPage($url);

        $I->seeResponseCodeIs($example['code']);   
        $I->seeCurrentUrlEquals($example['endpoint']);   
    }  

    /**
     * Logout after each test
     */
    public function _after(FunctionalTester $I)
    {
        $I->amLoggedOut();
    }
}
