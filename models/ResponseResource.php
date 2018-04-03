<?php

/**
 * A resource for a process response
 */
class ResponseResource extends ExternalResource
{
    /**
     * @var object
     * @required
     */
    public $process;
    
    /**
     * @var object
     * @required
     */
    public $actor;
    
    
    /**
     * Get the identifier
     * 
     * @return string
     */
    public function getId()
    {
        return $this->process->id;
    }

    
    /**
     * Set the identity that created this (version of the) resource.
     * 
     * @param Identity $identity
     * @return $this
     */
    public function setIdentity(Identity $identity)
    {
        $this->identity = $identity;
        
        if (isset($this->actor) && $this->actor->id === $identity->id) {
            $values = array_without($identity->getValues(), ['privileges', 'timestamp']);

            foreach ($values as $key => $value) {
                $this->actor->$key = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate the response.
     * 
     * @return Jasny\ValidationResult
     */
    public function validate()
    {
        $validation = parent::validate();
        
        if (isset($this->actor->id) && $this->actor->id !== $this->identity->id) {
            $validation->addError("actor id doesn't match identity id");
        }
        
        return $validation;
    }
    
    /**
     * Remove identity in json output
     * 
     * @param \stdClass $object
     * @return \stdClass
     */
    public function jsonSerializeFilterIdentity(\stdClass $object)
    {
        unset($object->id, $object->identity);
        
        return $object;
    }
}
