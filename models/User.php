<?php

/**
 * User entity
 *
 * @entitySet UserSet
 */
class User extends MongoDocument implements 
    Jasny\Auth\User, 
    Jasny\Authz\User
{ 
    /**
     * @var string
     * @dbFieldType \MongoId
     * @immutable
     */
    public $id;

    /**
     * @var string
     * @required
     * @searchField
     */
    public $first_name;

    /**
     * @var string
     * @required
     * @searchField
     */
    public $last_name;

    /**
     * @var string
     * @required
     * @unique
     * @searchField
     */
    public $email;

    /**
     * @var string
     * @required
     */
    public $password;

    /**
     * User access level
     * @var int
     * @required
     **/
    public $access_level = 1;

     /**
     * @var boolean
     */
    public $active = false;

    /**
     * Time of user creation
     * @var DateTime
     **/
    public $created_date;
    
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (empty($this->id)) {
            $this->id = (string)(new MongoId());
        }
    }

    /**
     * Cast record to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * Get user full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the usermame
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Get the hashed password
     * 
     * @return string
     */
    public function getHashedPassword()
    {
        return $this->password;
    }

    /**
     * Event called on login.
     * 
     * @return boolean  false cancels the login
     */
    public function onLogin()
    {
        return true;
    }

    /**
     * Event called on logout.
     */
    public function onLogout()
    {
        
    }

    /**
     * Get user image path
     *
     * @param string $size
     * @return string
     */
    public function getImage($size = '')
    {
        return '';
    }

    /**
     * Get user access level
     *
     * @return int
     */
    public function getRole()
    {
        return $this->access_level;
    }

    /**
     * Add a social network id to this user
     * 
     * @param string        $service
     * @param Social\User   $me     User profile
     * @return $this
     */
    public function addSocialNetwork($service, Social\User $socialUser)
    {
        $this->{$service . '_id'} = $socialUser->getId();
        
        // Add (missing) profile information
        if ($this->isNew()) {
            $this->first_name = $socialUser->getFirstName();
            $this->last_name = $socialUser->getLastName();
            $this->email = $socialUser->getEmail();
        }
        
        return $this;
    }

    /**
     * Save user
     *
     * @param array $opts
     * @return $this
     */
    public function save(array $opts = [])
    {
        if (!$this->created_date) {
            $this->created_date = new DateTime();
        }

        return parent::save();
    }
}
