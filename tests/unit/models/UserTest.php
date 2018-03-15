<?php

/**
 * @covers User
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test user creation
     */
    public function testCreate()
    {
        $user = User::create();
        $user->setValues([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com'
        ]);
        
        $this->assertEquals('John Doe', $user->getFullName(), "Full name is not correct");
        $this->assertEquals('John Doe', (string)$user, "Casting to string is ot correct");
        $this->assertEquals('test@example.com', $user->email, "Email was not set correctly");
        $this->assertTrue(MongoDocument::isValidMongoId($user->getId()), "User id should be set");
    }

    /**
     * Test 'getUsername' method
     */
    public function testGetUsername()
    {
        $user = User::create();
        $user->email = 'test@example.com';

        $this->assertEquals('test@example.com', $user->getUsername());
    }

    /**
     * Test 'getHashedPassword' method
     */
    public function testGetHashedPassword()
    {
        $user = User::create();
        $user->password = 'Hashed password';

        $this->assertEquals('Hashed password', $user->getHashedPassword());
    }

    /**
     * Test 'onLogin' method
     */
    public function testOnLogin()
    {
        $user = User::create();

        $this->assertTrue($user->onLogin());
    }

    /**
     * Test 'getImage' method
     */
    public function testGetImage()
    {
        $user = User::create();

        $this->assertEquals('', $user->getImage());
    }

    /**
     * Test 'getRole' method
     */
    public function testGetRole()
    {
        $user = User::create();
        $user->access_level = 100;

        $this->assertEquals(100, $user->getRole());
    }

    /**
     * Test 'addSocialNetwork' method
     */
    public function testAddSocialNetwork()
    {
        $user = $this->getMockBuilder(User::class)->setMethods(['isNew'])->getMock();
        $user->expects($this->once())->method('isNew')->willReturn(true);

        $socialUser = $this->createConfiguredMock(\Social\Facebook\User::class, [
            'getId' => 'a',
            'getFirstName' => 'John',
            'getLastName' => 'Doe',
            'getEmail' => 'test@example.com'
        ]);

        $user->addSocialNetwork('facebook', $socialUser);

        $this->assertEquals('a', $user->facebook_id);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('test@example.com', $user->email);
    }

    /**
     * Test 'addSocialNetwork' method, if user is not new
     */
    public function testAddSocialNetworkNotNew()
    {
        $user = $this->getMockBuilder(User::class)->setMethods(['isNew'])->getMock();
        $user->expects($this->once())->method('isNew')->willReturn(false);

        $user->setValues([
            'first_name' => 'Teddy',
            'last_name' => 'Bear',
            'email' => 'foo@example.com'
        ]);

        $socialUser = $this->createConfiguredMock(\Social\Facebook\User::class, [
            'getId' => 'a',
            'getFirstName' => 'John',
            'getLastName' => 'Doe',
            'getEmail' => 'test@example.com'
        ]);

        $user->addSocialNetwork('facebook', $socialUser);

        $this->assertEquals('a', $user->facebook_id);
        $this->assertEquals('Teddy', $user->first_name);
        $this->assertEquals('Bear', $user->last_name);
        $this->assertEquals('foo@example.com', $user->email);
    }
}
