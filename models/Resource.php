<?php

use Jasny\DB\Entity;
use Jasny\DB\Entity\Redactable;
use Jasny\DB\Entity\Meta;

/**
 * Resource interface
 */
interface Resource extends Entity, Redactable, Meta
{
    /**
     * Apply privilege, removing properties if needed.
     * 
     * @param Privilege $privilege
     */
    public function applyPrivilege(Privilege $privilege);
 
    /**
     * Set the identity that created this (version of the) resource.
     * 
     * @param Identity $identity
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
