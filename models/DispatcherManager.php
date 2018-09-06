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
     * Class constructor
     * 
     * @param Dispatcher $dispatcher
     * @param Account $nodeAccount
     */
    public function __construct(Dispatcher $dispatcher, Account $nodeAccount)
    {
        $this->dispatcher = $dispatcher;
        $this->node = $nodeAccount;
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
        
        if (!$chain->isEventSignedByAccount($event, $this->node)) {
            return;
        }
        
        $this->dispatcher->queue($chain, $nodes);
    }
}
