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
    protected $account;
    
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    
    /**f
     * Class constructor
     * 
     * @param Dispatcher $dispatcher
     * @param Account $account
     * @param ResourceFactor $resourceFactory
     */
    public function __construct(Dispatcher $dispatcher, Account $account, ResourceFactory $resourceFactory)
    {
        $this->dispatcher = $dispatcher;
        $this->account = $account;
        $this->resourceFactory = $resourceFactory;
    }
    
    
    /**
     * Send the event chain to the dispatcher service
     * 
     * @param EventChain $chain
     */
    public function dispatch(EventChain $chain)
    {
        $event = $chain->getLastEvent();
        
        if (!$event || !$event->signkey) {
            return;
        }
        
        if (!$this->shouldDispatch($chain)) {
            return;
        }
        
        $resource = $this->resourceFactory->extractFrom($event);

        if ($resource instanceof Identity) {
            return $this->dispatcher->queue($chain, $chain->getNodes());
        }
        
        $this->dispatcher->queue($chain->withEvents([$event]), $chain->getNodes());
    }

    /**
     * Check if the identity who created the event belongs to this node. If so the event should be dispatched.
     *
     * @param EvenChain $chain
     * @return bool
     */
    protected function shouldDispatch(EventChain $chain)
    {
        $event = $chain->getLastEvent();

        if ($this->account->getPublicSignKey() === $event->signkey) {
            return true;
        }

        if ($chain->hasSystemKeyForIdentity($event->signkey,  $this->account->getPublicSignKey())) {
            return true;
        }

        return false;
    }
}
