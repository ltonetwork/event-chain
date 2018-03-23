<?php

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Redactable;

/**
 * Identity entity
 */
class Identity extends MongoSubDocument implements Identifiable, Resource
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
    
    
    /**
     * Get the unique identifier of the Identity
     * 
     * @param boolean $withVersion  Not used, identities aren't versioned
     * @return string
     */
    public function getId($withVersion = false)
    {
        return parent::getId();
    }
    
    /**
     * Apply privilege, removing properties if needed.
     * 
     * @param Privilege $privilege
     */
    public function applyPrivilege(Privilege $privilege)
    {
        if (isset($privilege->only)) {
            $this->withOnly(...$privilege->only);
        }
        
        if (isset($privilege->not)) {
            $this->without(...$privilege->not);
        }
    }
}
