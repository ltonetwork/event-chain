<?php declare(strict_types=1);

use Jasny\DB\Entity;
use Jasny\DB\Entity\Redactable;
use Jasny\DB\Entity\Meta;
use Jasny\DB\Entity\Validation;

/**
 * ResourceInterface interface
 */
interface ResourceInterface extends Entity, Redactable, Meta, Validation
{
    /**
     * Get the resource JSONSchema declaration.
     *
     * @return string
     */
    public function getSchema(): string;

    /**
     * Apply privilege, removing properties if needed.
     *
     * @param Privilege $privilege
     * @return $this
     */
    public function applyPrivilege(Privilege $privilege);
 
    /**
     * Set the identity that created this (version of the) resource.
     *
     * @param Identity $identity
     * @return $this
     */
    public function setIdentity(Identity $identity);
    
    
    /**
     * Extract an identity from an event
     *
     * @param Event $event
     * @return static
     */
    public static function fromEvent(Event $event);
}
