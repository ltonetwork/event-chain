<?php

/**
 * Resource interface
 */
interface Resource
{
    /**
     * Get the id
     * 
     * @param boolean $withVersion
     */
    public function getId($withVersion = false);
    
    /**
     * Apply privilege, removing properties if needed.
     * 
     * @param Privilege $privilege
     */
    public function applyPrivilege(Privilege $privilege);
}
