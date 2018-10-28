<?php declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;

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
     * @var string|null
     */
    public $id;
    
    /**
     * Only these properties
     * @var string[]|null
     */
    public $only;
    
    /**
     * Not these properties
     * @var string[]|null
     */
    public $not;
    
    
    /**
     * Class constructor
     *
     * @param string|ResourceInterface $schema
     * @param string|null              $id
     */
    public function __construct($schema = null, ?string $id = null)
    {
        if ($schema instanceof ResourceInterface) {
            $id = $schema instanceof Identifiable ? $schema->getId() : null;
            $schema = $schema->getSchema();
        }
        
        $this->schema = $schema;
        $this->id = $id;

        parent::__construct();
    }
    
    /**
     * Check if privilege matches schema and id
     *
     * @param string      $schema
     * @param string|null $id
     * @return bool
     */
    public function match(string $schema, ?string $id = null): bool
    {
        return (!isset($this->schema) || $this->schema === $schema)
            && (!isset($id) || !isset($this->id) || $this->id === $id);
    }
    
    
    /**
     * Combine privileges.
     *
     * @param Privilege[] $privileges
     * @return $this
     */
    public function consolidate(array $privileges): self
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
     * @return bool
     */
    protected function consolidateOnly(Privilege $privilege): bool
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
     * @return bool
     */
    protected function consolidateNot(Privilege $privilege): bool
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
     * @return bool
     */
    protected function consolidateAll(): bool
    {
        $this->only = null;
        $this->not = null;
        
        return true;
    }
}
