<?php

use Jasny\ValidationResult;

/**
 * Event entity
 */
class Event extends MongoDocument
{
    /**
     * Base58 encoded JSON string with the body of the event.
     * 
     * @var string
     * @required
     */
    public $body;
    
    /**
     * Time when the event was signed.
     * 
     * @var DateTime
     * @required
     */
    public $timestamp;
    
    /**
     * Hash to the previous event
     * 
     * @var string
     * @required
     */
    public $previous;
    
    /**
     * Base58 encoded X25519 public key used to sign the event
     * 
     * @var string
     * @required
     */
    public $signkey;
    
    /**
     * Base58 encoded signature of the event
     * 
     * @var string
     * @required
     */
    public $signature;
    
    /**
     * SHA256 hash of the event
     * 
     * @var string
     * @required
     */
    public $hash;
    
    /**
     * Receipt for anchoring on public blockchain
     * 
     * @var Receipt
     * @immutable
     */
    public $receipt;
    
    
    /**
     * @inheritDoc
     */
    public function setValues($values)
    {
        if (!$this->isNew()) {
            throw new BadMethodCallException("Event is immutable");
        }
        
        return parent::setValues($values);
    }
    
    /**
     * Add a receipt to the event
     * 
     * @param Receipt $receipt
     * @return $this
     */
    public function addReceipt(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }
    
    /**
     * Get the message used for hash and signature
     * 
     * @return string
     */
    protected function getMessage()
    {
        return join("\n", [
            $this->body,
            $this->timestamp ? $this->timestamp->format('c') : '',
            $this->previous,
            $this->signkey
        ]);
    }
    
    /**
     * Get the base58 encoded hash of the event
     * 
     * @return string
     */
    public function getHash()
    {
        $hash = hash('sha256', $this->getMessage(), true);

        $base58 = new StephenHill\Base58();
        return $base58->encode($hash);
    }
    
    /**
     * Get the decoded body
     * 
     * @return object|false
     */
    public function getBody()
    {
        $base58 = new StephenHill\Base58();
        $json = $base58->decode($this->body);
        
        return $json ? json_decode($json) : null;
    }
    
    /**
     * Verify that the signature is valid
     * 
     * @return boolean
     */
    public function verifySignature()
    {
        if (!isset($this->signature) || !isset($this->signkey)) {
            return false;
        }
        
        $base58 = new StephenHill\Base58();
        
        $signature = $base58->decode($this->signature);
        $signkey = $base58->decode($this->signkey);
        
        // TODO: Convert X25519 key to ED25519
        // $signkey = jasny\curve25519_pk_to_ed25519($signkey);
        
        //return sodium\crypto_sign_verify_detached($this->signature, $this->getMessage(), $signkey);
        return strlen($this->signature) === 44;
    }
    
    /**
     * @inheritDoc
     */
    public function validate()
    {
        $validation = parent::validate();
        
        if (isset($this->body) && $this->getBody() === null) {
            $validation->addError('body is not base58 encoded json');
        }
        
        if (isset($this->signature) && isset($this->signkey) && !$this->verifySignature()) {
            $validation->addError('invalid signature');
        }
        
        if (isset($this->hash) && $this->getHash() !== $this->hash) {
            $validation->addError('invalid hash');
        }
        
        if (isset($this->receipt) && $this->receipt->targetHash !== $this->hash) {
            $validation->add(ValidationResult::error("Hash doesn't match"), "Invalid receipt");
        }
        
        return $validation;
    }
}
