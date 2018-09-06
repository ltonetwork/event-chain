<?php

use LTO\Account;

/**
 * Manage the dispatcher
 */
class DispatcherManager
{
    /**
     * @var Account
     */
    protected $node;
    
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    
    /**
     * Class constructor
     * 
     * @param Dispatcher $dispatcher
     * @param Account $nodeAccount
     * @param ResourceFactor $resourceFactory
     */
    public function __construct(Dispatcher $dispatcher, Account $nodeAccount, ResourceFactory $resourceFactory)
    {
        $this->dispatcher = $dispatcher;
        $this->node = $nodeAccount;
        $this->resourceFactory = $resourceFactory;
    }
    
    
    /**
     * Send the event chain to the dispatcher service
     * 
     * @param EventChain $chain
     * @param string[]   $nodes
     */
    public function dispatch(EventChain $chain, $nodes)
    {
        $event = $chain->getLastEvent();
        
        if (!$event || !$event->signkey) {
            return;
        }
        
        if (!$this->shouldDispatch($chain, $event)) {
            return;
        }
        
        $this->dispatcher->queue($chain, $nodes);
    }

    /**
     * Check if the identity who created the event belongs to this node.
     * If so the event should be dispatched.
     *
     * @param EvenChain $chain
     * @param Event     $event
     * 
     * @return bool
     */
    protected function shouldDispatch(EventChain $chain, $event)
    {
        if ($event->signkey === $this->node->getPublicSignKey()) {
            return true;
        }

        if ($chain->hasSystemKeyForIdentity($event->signkey,  $this->node->getPublicSignKey())) {
            return true;
        }

        return false;
    }
}
