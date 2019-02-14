<?php declare(strict_types=1);

use Jasny\ValidationResult;

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
    public function getId(): string
    {
        if (!isset($this->process->id)) {
            throw new BadMethodCallException("Process id not set");
        }

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
        
        if (isset($this->actor) && (!isset($this->actor->id) || $this->actor->id === $identity->id)) {
            $values = array_without($identity->getValues(), ['privileges', 'timestamp']);

            foreach ($values as $key => $value) {
                /** @noinspection PhpVariableVariableInspection */
                $this->actor->$key = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate the response.
     *
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $validation = parent::validate();
        
        if (!isset($this->process->id)) {
            $validation->addError("process id not set");
        }

        // FIXME: needs to work for multiple identities with same system signkey
        /*if (isset($this->actor->id) && $this->actor->id !== $this->identity->id) {
            $validation->addError("actor '%s' is assigned to identity '%s', not '%s'", $this->actor->key,
                $this->actor->id, $this->identity->id);
        }*/
        
        return $validation;
    }
    
    /**
     * Remove identity in json output
     *
     * @param stdClass $object
     * @return stdClass
     */
    public function jsonSerializeFilterIdentity(stdClass $object): stdClass
    {
        unset($object->id, $object->identity);
        
        return $object;
    }
}
