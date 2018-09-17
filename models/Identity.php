<?php

use Jasny\DB\Entity\Identifiable;

/**
 * Identity entity
 */
class Identity extends MongoSubDocument implements Resource, Identifiable
{
    use ResourceBase;
    
    /**
     * Unique identifier
     * @var string
     * @pattern [0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}
     */
    public $id;
    
    /**
     * Person / organization info
     * @var \stdClass
     */
    public $info;

    /**
     * Live contracts node the identity is using
     * @var string
     * @required
     */
    public $node;
    
    /**
     * Name of the identity
     * @var string
     */
    public $name;
    
    /**
     * Email address of the identity
     * @var string
     */
    public $email;
    
    /**
     * Cryptographic (ED25519) public keys used in signing
     * @var array
     */
    public $signkeys = [];
    
    /**
     * Cryptographic (X25519) public key used for encryption
     * @var string
     */
    public $encryptkey;
    
    /**
     * Privileges
     * @var Privilege[]|EntitySet
     */
    public $privileges;
    
    /**
     * Get id property
     * 
     * @return string
     */
    public static function getIdProperty()
    {
        return 'id';
    }
}
