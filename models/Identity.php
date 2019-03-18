<?php declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\EntitySet;

/**
 * Identity entity
 */
class Identity extends MongoSubDocument implements ResourceInterface, Identifiable
{
    use ResourceBase;
    
    /**
     * Unique identifier
     * @var string
     */
    public $id;

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
     * @var EntitySet&iterable<Privilege>
     */
    public $privileges;
    
    /**
     * Get id property
     *
     * @return string
     */
    public static function getIdProperty(): string
    {
        return 'id';
    }
}
