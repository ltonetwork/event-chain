<?php declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;

/**
 * Stub class to prevent interactions with anchor service.
 */
class NoAnchorClient extends AnchorClient
{    
    /**
     * Class constructor
     */
    public function __construct()
    {

    }    
    
    /**
     * Anchor the given hash.
     *
     * @param string $hash
     * @param string $encoding
     */
    public function submit(string $hash, string $encoding = 'base58'): void
    {
        // Just silently ignore action
    }

    /**
     * Fetch anchor information.
     *
     * @param string $hash
     * @param string $encoding
     * @throws Exception 
     */
    public function fetch(string $hash, string $encoding = 'base58'): ?stdClass
    {
        $this->throwException();
    }

    /**
     * Fetch anchor information for multiple hashes.
     * If a hash isn't anchored it's omitted from the result.
     *
     * @param iterable<string> $hashes
     * @param string           $encoding
     * @throws Exception
     */
    public function fetchMultiple(iterable $hashes, string $encoding = 'base58'): Pipeline
    {
        $this->throwException();
    }

    /**
     * Throw exception
     *
     * @throws Exception 
     */
    protected function throwException()
    {
        throw new Exception("Unable to fetch information from anchoring service. The event-chain service runs in a local-only setup (anchor disabled).");
    }
}
