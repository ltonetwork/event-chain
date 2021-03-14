<?php declare(strict_types=1);

/**
 * Class to prevent interaction with event dispatcher service
 */
class NoDispatcher extends Dispatcher
{   
    /**
     * Class constructor
     *
     * @codeCoverageIgnore
     */ 
    public function __construct()
    {

    }

    /**
     * Get info about the dispatcher
     *
     * @throws Exception 
     */
    public function info(): stdClass
    {
        $this->throwException();
    }

    /**
     * Get the node url that the dispatcher is running on
     *
     * @throws Exception 
     */
    public function getNode(): string
    {
        $this->throwException();
    }
    
    /**
     * Add the event to the queue of the node
     *
     * @throws Exception 
     * @param EventChain $chain
     * @param string[]   $to     If specified will send the event to the nodes in this array
     */
    public function queue(EventChain $chain, ?array $to = null): void
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
        throw new Exception("Unable to dispatch events. The event-chain service runs in a local-only setup (queuer disabled). Make sure all identities are using system key 'YOUR KEY HERE'");
    }
}
