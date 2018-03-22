<?php

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Redactable;

/**
 * Identity entity
 */
class Identity extends MongoSubDocument implements Identifiable, Redactable
{
    use Redactable\Implementation;
    
    /**
     * The JSON schema of an identity
     */
    const SCHEMA = 'http://specs.livecontracts.io/draft-01/identity/schema.json#';
    
    /**
     * Unique identifier
     * @var string
     * @pattern [0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}
     */
    public $id;
    
    /**
     * Person / organization name
     * @var string
     */
    public $name;

    /**
     * E-mail address
     * @var string
     * @type email
     */
    public $email;
    
    /**
     * Live contracts node the identity is using
     * @var string
     */
    public $node;
    
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
}
