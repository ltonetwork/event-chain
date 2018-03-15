<?php

/**
 * @covers Auth
 */
class AuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Auth|PHPUnit_Framework_MockObject_MockObject
     */
    protected $auth;
    
    public function setUp()
    {
        $this->auth = $this->createPartialMock(Auth::class, ['persistCurrentUser', 'getSessionData']);
    }
    
    /**
     * Test if user has user level of access
     */
    public function testIsWithoutUser()
    {
        $this->assertTrue($this->auth->is('guest'), "No user means it's a guest");
        $this->assertFalse($this->auth->is('user'), "User should not have 'user' level");
        $this->assertFalse($this->auth->is('admin'), "User should not have 'admin' level");
    }
    
    /**
     * Test if user has user level of access
     */
    public function testIsWithUser()
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn(1);
        $user->method('onLogin')->willReturn(true);

        $this->auth = $this->getMockBuilder(Auth::class)->setMethods(['persistCurrentUser'])->getMock();
        $this->auth->setUser($user);
        
        $this->assertFalse($this->auth->is('guest'), "User is not a guest");
        $this->assertTrue($this->auth->is('user'), "User should have 'user' level");
        $this->assertFalse($this->auth->is('admin'), "User should not have 'admin' level");
    }

    /**
     * Test if user has all levels of access
     */
    public function testIsWithAdmin()
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn(100);
        $user->method('onLogin')->willReturn(true);

        $this->auth = $this->getMockBuilder(Auth::class)->setMethods(['persistCurrentUser'])->getMock();
        $this->auth->setUser($user);
        
        $this->assertFalse($this->auth->is('guest'), "User is not a guest");
        $this->assertTrue($this->auth->is('user'), "User should have 'user' level");
        $this->assertTrue($this->auth->is('admin'), "User should have 'admin' level");
    }

    /**
     * Test generating random password
     */
    public function testGeneratePassword()
    {
        $password = $this->auth->generatePassword();

        $this->assertEquals(20, strlen($password));
        $this->assertTrue((bool)preg_match('|^[a-z0-9]+$|i', $password));
    }

    /**
     * Test 'getAccessLevels' method
     */
    public function testGetAccessLevels()
    {
        $levels = $this->auth->getAccessLevels();

        $this->assertEquals([
            'guest' => -1,
            'user' => 1,
            'admin' => 100
        ], $levels);
    }

    /**
     * Provide data for testing 'isUser' method
     *
     * @return array
     */
    public function isUserProvider()
    {
        return [
            [1, ['user'], ['admin']],
            [100, ['user', 'admin'], []],
        ];
    }

    /**
     * Test arbitrary user access level
     *
     * @dataProvider isUserProvider
     */
    public function testIsUser($role, $is, $isNot)
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn($role);

        foreach ($is as $level) {
            $this->assertTrue($this->auth->isUser($user, $level));
        }

        foreach ($isNot as $level) {
            $this->assertFalse($this->auth->isUser($user, $level));
        }
    }

    /**
     * Provide data for testing 'isUser' method
     *
     * @return array
     */
    public function isUserExactProvider()
    {
        return [
            [1, ['user'], ['admin']],
            [100, ['admin'], ['user']],
        ];
    }

    /**
     * Test arbitrary user access level
     *
     * @dataProvider isUserExactProvider
     */
    public function testIsUserExact($role, $is, $isNot)
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn($role);

        foreach ($is as $level) {
            $this->assertTrue($this->auth->isUser($user, $level, true));
        }

        foreach ($isNot as $level) {
            $this->assertFalse($this->auth->isUser($user, $level, true));
        }
    }

    /**
     * Provide data for testing 'isUser' method with wrong roles
     *
     * @return array
     */
    public function isUserInvalidRoleProvider()
    {
        return [
            ['guest'],
            ['invalid-role']
        ];
    }

    /**
     * Test 'isUser' method with wrong roles
     *
     * @expectedException InvalidArgumentException
     * @dataProvider isUserInvalidRoleProvider
     * @param string $role
     */
    public function testIsUserInvalidRole($role)
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn('user');

        $this->auth->isUser($user, $role);
    }

    /**
     * Test 'isUser' method with wrong roles
     *
     * @expectedException DomainException
     */
    public function testIsUserOwnInvalidRole()
    {
        $user = $this->createMock(User::class);
        $user->method('getRole')->willReturn('invalid-role');

        $this->auth->isUser($user, 'user');
    }
}
