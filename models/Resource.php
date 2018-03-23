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
     * Apply privileges, removing properties if needed.
     * 
     * @param Privilege[] $privileges
     */
    public function applyPrivileges(array $privileges);
}
