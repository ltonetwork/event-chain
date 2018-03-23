<?php

namespace Resource;

/**
 * Resource base class
 */
abstract class Base implements \Resource
{
    /**
     * Apply privileges, removing properties if needed.
     * 
     * @param \Privilege[] $privileges
     */
    public function applyPrivileges(array $privileges)
    {
    }
}
