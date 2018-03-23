<?php

namespace Resource;

/**
 * Resource base class
 */
abstract class Base implements \Resource
{
    /**
     * Apply privilege, removing properties if needed.
     * 
     * @param \Privilege[] $privilege
     */
    public function applyPrivileges(\Privilege $privilege)
    {
    }
}
