<?php

/**
 * Privilege entity
 */
class Privilege extends MongoSubDocument
{
    /**
     * Match resource schema
     * @var string 
     */
    public $schema;
    
    /**
     * Match resource id
     * @var string 
     */
    public $id;
    
    /**
     * Only these properties
     * @var string[]
     */
    public $only;
    
    /**
     * Not these properties
     * @var string[]
     */
    public $not;
    
    
    /**
     * Class constructor
     * 
     * @param string $schema
     * @param string $id
     */
    public function __construct($schema = null, $id = null)
    {
        $this->schema = $schema;
        $this->id = $id;
    }
    
    /**
     * Check if privilege matches schema and id
     * 
     * @param string $schema
     * @param string $id
     */
    public function match($schema, $id = null)
    {
        return (!isset($this->schema) || $this->schema === $schema)
            && (!isset($id) || !isset($this->id) || $this->id === $id);
    }
    
    
    /**
     * Combine privileges
     * 
     * @param Privilege[] $privileges
     */
    public function consolidate(array $privileges)
    {
        $this->only = [];
        $this->not = null;
        
        foreach ($privileges as $privilege) {
            $this->consolidateOnly($privilege)
                || $this->consolidateNot($privilege)
                || $this->consolidateAll();
            
            if ($this->only === null && $this->not === null) {
                break;
            }
        }
        
        return $this;
    }
    
    /**
     * Combine a privilege with a 'only' property
     * 
     * @param Privilege $privilege
     * @return boolean
     */
    protected function consolidateOnly(Privilege $privilege)
    {
        if (!isset($privilege->only)) {
            return false;
        }
        
        $only = !isset($privilege->not) ? $privilege->only : array_diff($privilege->only, $privilege->not);
        
        if (isset($this->not)) {
            $this->not = array_diff($this->not, $only) ?: null;
        } else {
            $this->only = array_unique(array_merge($this->only, $only));
        }
        
        return true;
    }
    
    /**
     * Combine a privilege with a 'not' property
     * 
     * @param Privilege $privilege
     * @return boolean
     */
    protected function consolidateNot(Privilege $privilege)
    {
        if (!isset($privilege->not)) {
            return false;
        }
        
        if (isset($this->only)) {
            $this->not = array_diff($privilege->not, $this->only) ?: null;
            $this->only = null;
        } else {
            $this->not = array_intersect($this->not, $privilege->not) ?: null;
        }
        
        return true;
    }
    
    /**
     * Combine a privilege without limitations
     * 
     * @return boolean
     */
    protected function consolidateAll()
    {
        $this->only = null;
        $this->not = null;
        
        return true;
    }
}
