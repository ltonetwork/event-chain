<?php

class AuthControllerCest extends BaseAuthz
{
    /**
     * Provide data for testing viewing login page
     *
     * @return array
     */
    protected function loginProvider()
    {
        $url = '/login';
        $data = [
            'email' => 'test-user@example.com',
            'password' => 'password'
        ];

        return [
            ['email' => '', 'code' => 200, 'data' => $data, 'endpoint' => '/'],
            ['email' => 'test-user@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url],
        ];
    }

    /**
     * Test viewing login page
     *
     * @dataprovider loginProvider
     */
    public function showLogin(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/login', $I, $example, true);
    }

    /**
     * Provide data for testing viewing login page
     *
     * @return array
     */
    protected function logoutProvider()
    {
        return [
            ['email' => '', 'code' => 401, 'endpoint' => '/logout'],
            ['email' => 'test-user@example.com', 'code' => 200, 'endpoint' => '/'],
            ['email' => 'test-admin@example.com', 'code' => 200, 'endpoint' => '/'],
        ];
    }

    /**
     * Test going to logout url
     *
     * @dataprovider logoutProvider
     */
    public function logout(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/logout', $I, $example);
    }

    /**
     * Provide data for testing viewing forgot-password page
     *
     * @return array
     */
    protected function forgotPasswordProvider()
    {
        $url = '/forgot-password';
        $data = [
            'email' => 'test-user@example.com'
        ];

        return [
            ['email' => '', 'code' => 200, 'data' => $data, 'endpoint' => '/'],
            ['email' => 'test-user@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 403, 'data' => $data, 'endpoint' => $url],
        ];
    }

    /**
     * Test going to forgot-password page
     *
     * @dataprovider forgotPasswordProvider
     */
    public function forgotPassword(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/forgot-password', $I, $example, true);
    }
}
