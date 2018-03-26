<?php

/**
 * An account (aka wallet)
 */
class Account
{
    /**
     * Account public address
     * @var string
     */
    public $address;
    
    /**
     * Sign keys
     * @var object
     */
    public $sign;
    
    /**
     * Encryption keys
     * @var object
     */
    public $encrypt;
    
    
    /**
     * Get base58 encoded address
     * 
     * @return string
     */
    public function getAddress()
    {
        return $this->address ? static::base58($this->address) : null;
    }
    
    /**
     * Get base58 encoded public sign key
     * 
     * @return string
     */
    public function getPublicSignKey()
    {
        return $this->sign ? static::base58($this->sign->publickey) : null;
    }
    
    /**
     * Get base58 encoded public encryption key
     * 
     * @return string
     */
    public function getPublicEncryptKey()
    {
        return $this->encrypt ? static::base58($this->encrypt->publickey) : null;
    }
    
    
    /**
     * Create a base58 encoded signature of a message.
     * 
     * @param string $message
     * @return string
     */
    public function sign($message)
    {
        if (!isset($this->sign->secretkey)) {
            throw new RuntimeException("Unable to sign message; no secret sign key");
        }
        
        $signature = sodium\crypto_sign_detached($message, $this->sign->secretkey);
        
        return static::base58($signature);
    }
    
    
    /**
     * Encrypt a message for another account.
     * 
     * @param Account $recipient 
     * @param string  $message
     * @return string
     */
    public function encryptFor(Account $recipient, $message)
    {
        if (!isset($this->encrypt->private)) {
            throw new RuntimeException("Unable to encrypt message; no secret encryption key");
        }
        
        if (!isset($recipient->encrypt->publickey)) {
            throw new RuntimeException("Unable to encrypt message; no public encryption key for recipient");
        }
        
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

        $encryption_key = sodium\crypto_box_keypair_from_secretkey_and_publickey($this->encrypt->private,
            $recipient->encrypt->publickey);
        
        return sodium\crypto_box($message, $nonce, $encryption_key);
    }
    
    /**
     * Decrypt a message from another account.
     * 
     * @param Account $sender 
     * @param string  $message
     * @return string
     */
    public function decryptFrom(Account $sender, $message)
    {
        if (!isset($this->encrypt->private)) {
            throw new RuntimeException("Unable to decrypt message; no secret encryption key");
        }
        
        if (!isset($sender->encrypt->publickey)) {
            throw new RuntimeException("Unable to decrypt message; no public encryption key for recipient");
        }
        
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

        $encryption_key = sodium\crypto_box_keypair_from_secretkey_and_publickey($sender->encrypt->publickey,
            $this->encrypt->private);
        
        return sodium\sodium_crypto_box_open($message, $nonce, $encryption_key);
    }
    
    
    /**
     * Base58 encode a string
     * 
     * @param string $string
     * @return string
     */
    protected static function base58($string)
    {
        $base58 = new \StephenHill\Base58();
        
        return $base58->encode($string);
    }
}
