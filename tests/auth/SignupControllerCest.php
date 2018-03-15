<?php

class SignupControllerCest extends BaseAuthz
{
    /**
     * Provide data for testing going to register page
     *
     * @return array
     */
    protected function signupProvider()
    {
        $url = '/signup';
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'some-password'
        ];

        return [
            ['email' => '', 'code' => 200, 'data' => $data, 'endpoint' => '/'],
            ['email' => 'test-user@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url]
        ];
    }

    /**
     * Test going to register page
     *
     * @dataprovider signupProvider
     */
    public function signup(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/signup', $I, $example, true);
    }
}
