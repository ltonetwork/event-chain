<?php

class DefaultControllerCest extends BaseAuthz
{
    /**
     * Provide data for testing going to base page
     *
     * @return array
     */
    protected function indexProvider()
    {
        return [
            ['email' => '', 'code' => 200, 'endpoint' => '/'],
            ['email' => 'test-user@example.com', 'code' => 200, 'endpoint' => '/'],
            ['email' => 'test-admin@example.com', 'code' => 200, 'endpoint' => '/'],
        ];
    }

    /**
     * Test going to base page
     *
     * @dataprovider indexProvider
     */
    public function index(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/', $I, $example);
    }
}
