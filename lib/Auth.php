<?php

use Jasny\Authz;

class Auth extends Jasny\Auth implements Authz
{
    use Auth\Social,
        Jasny\Auth\Sessions,
        Jasny\Auth\Confirmation,
        Jasny\Authz\ByLevel
    {
        Jasny\Authz\ByLevel::is as private _is;
    }

    /**
     * Get the access levels as [key => level]
     * 
     * @return array
     */
    public function getAccessLevels()
    {
        return [
            'guest' => -1,
            'user' => 1,
            'admin' => 100
        ];
    }
    
    /**
     * Fetch a user by ID
     * 
     * @param int $id
     * @return Jasny\Auth\User
     */
    public function fetchUserById($id)
    {        
        return User::fetch($id);
    }

    /**
     * Fetch a user by username (here - email)
     * 
     * @param string $email
     * @return Jasny\Auth\User
     */
    public function fetchUserByUsername($email)
    {
        return User::fetch(['email' => $email]);
    }

    /**
     * Get secret confirmation token
     * 
     * @return string
     */
    public function getConfirmationSecret()
    {
       return App::config()->secret->signup;
    }    

    /**
     * Check if the current user has a specific authorization level
     * 
     * @param string|int $level
     * @return boolean
     */
    public function is($level)
    {
        if ($this->getLevel($level) < 0) {
            return $this->user() === null;
        }
        
        return $this->_is($level);
    }

    /**
     * Check if given user has a specific authorization level
     *
     * @param User $user
     * @param string $role
     * @param boolean $exact
     * @return boolean
     */
    public function isUser(User $user, $role, $exact = false)
    {
        if (!in_array($role, $this->getRoles())) {
            throw new InvalidArgumentException("Invalid role for user: $role");            
        }

        if ($this->getLevel($role) < 0) {
            throw new InvalidArgumentException("Arbitrary user can not be a guest");            
        }

        $userLevel = $this->getLevel($user->getRole());
        $checkLevel = $this->getLevel($role);
        
        return $exact ? 
            $userLevel === $checkLevel :
            $userLevel >= $checkLevel;
    }

    /**
     * Generate random password
     *
     * @return string
     */
    public function generatePassword()
    {
        $bytes = openssl_random_pseudo_bytes(10); //password will be twice longer

        return bin2hex($bytes);
    }
}
