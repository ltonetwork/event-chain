<?php

class UserControllerCest extends BaseAuthz
{
    /**
     * Provide data for testing viewing edit user page
     *
     * @return array
     */
    protected function editUserProvider()
    {
        $url = '/settings';

        return [
            ['email' => '', 'code' => 401, 'endpoint' => $url],
            ['email' => 'test-user@example.com', 'code' => 200, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 200, 'endpoint' => $url],
        ];
    }

    /**
     * Test going to edit user page
     *
     * @dataprovider editUserProvider
     */
    public function editUser(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/settings', $I, $example);
    }

    /**
     * Provide data for testing viewing edit user password page
     *
     * @return array
     */
    protected function editUserPasswordProvider()
    {
        $url = '/settings/edit-password';

        return [
            ['email' => '', 'code' => 401, 'endpoint' => $url],
            ['email' => 'test-user@example.com', 'code' => 200, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 200, 'endpoint' => $url],
        ];
    }

    /**
     * Test going to edit user password page
     *
     * @dataprovider editUserPasswordProvider
     */
    public function editUserPassword(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/settings/edit-password', $I, $example);
    }

    /**
     * Provide data for testing viewing good-bye page
     *
     * @return array
     */
    protected function goodByeProvider()
    {
        $url = '/good-bye';

        return [
            ['email' => '', 'code' => 200, 'endpoint' => '/'],
            ['email' => '', 'code' => 200, 'endpoint' => '/good-bye', 'session' => true],
            ['email' => 'test-user@example.com', 'code' => 403, 'endpoint' => $url],
            ['email' => 'test-admin@example.com', 'code' => 403, 'endpoint' => $url],
        ];
    }

    /**
     * Test going to good-bye page
     *
     * @dataprovider goodByeProvider
     */
    public function goodBye(FunctionalTester $I, \Codeception\Example $example)
    {
        if (!empty($example['session'])) {
            $_SESSION['deleted'] = true;
        }

        $this->testUrl('/good-bye', $I, $example);

        unset($_SESSION['deleted']);
    }
    
    /**
     * Provide data for testing deleting user
     *
     * @return array
     */
    protected function deleteUserProvider()
    {
        $url = '/settings/delete';

        return [
            ['email' => '', 'code' => 401, 'endpoint' => $url],
            ['email' => 'test-user@example.com', 'code' => 200, 'endpoint' => '/good-bye'],
            ['email' => 'test-admin@example.com', 'code' => 200, 'endpoint' => '/good-bye'],
        ];
    }

    /**
     * Test deleting user
     *
     * @dataProvider deleteUserProvider
     */
    public function deleteUser(FunctionalTester $I, \Codeception\Example $example)
    {
        $this->testUrl('/settings/delete', $I, $example, true);
    }    
}
