<?php

use Jasny\ValidationResult;
use Jasny\DB\Entity\Identifiable;

/**
 * Event entity
 */
class Event extends MongoSubDocument implements Identifiable
{
    /**
     * Base58 encoded JSON string with the body of the event.
     * 
     * @var string
     * @required
     */
    public $body;

    /**
     * The extracted body (cached) as associated array
     * 
     * @var array|boolean
     */
    protected $cachedBody = false;

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
     * URI of the public key used to sign the event
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
     * @id
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
        
        $this->cachedBody = false; // Clear cached body
        
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
        
        return $this;
    }
    
    /**
     * Get the message used for hash and signature
     * 
     * @return string
     */
    protected function getMessage()
    {
        $message = join("\n", [
            $this->body,
            $this->timestamp ? $this->timestamp->format('c') : '',
            $this->previous,
            $this->signkey
        ]);
        
        return $message;
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
     * @return array|false
     */
    public function getBody()
    {
        if ($this->cachedBody !== false) {
            return $this->cachedBody;
        }
        
        if (!isset($this->body)) {
            return null;
        }
        
        $base58 = new StephenHill\Base58();
        $json = $base58->decode($this->body);
        
        $this->cachedBody = $json ? json_decode($json, true) : null;
        
        return $this->cachedBody;
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
        
        return strlen($signature) === sodium\CRYPTO_SIGN_BYTES &&
            strlen($signkey) === sodium\CRYPTO_SIGN_PUBLICKEYBYTES &&
            sodium\crypto_sign_verify_detached($signature, $this->getMessage(), $signkey);
    }
    
    /**
     * Validate the event
     * 
     * @return ValidationResult
     */
    public function validate()
    {
        $validation = parent::validate();
        
        $body = $this->getBody();
        if (isset($this->body) && $body === null) {
            $validation->addError('body is not base58 encoded json');
        }

        if (isset($body) && !isset($body['$schema'])) {
            $validation->addError('body is does not contain the $schema property');
        }
        
        if (isset($this->signature) && !$this->verifySignature()) {
            $validation->addError('invalid signature');
        }
        
        if (isset($this->hash) && $this->getHash() !== $this->hash) {
            $validation->addError('invalid hash');
        }
        
        if (isset($this->receipt)) {
            $validation->add($this->receipt->validate(), "invalid receipt;");
            
            if ($this->receipt->targetHash !== $this->hash) {
                $validation->add(ValidationResult::error("hash doesn't match"), "invalid receipt;");
            }
        }
        
        return $validation;
    }
}
