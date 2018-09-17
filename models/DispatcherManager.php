<?php

use LTO\Account;
use \Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Class constructor
     * 
     * @param Dispatcher $dispatcher
     * @param Account $nodeAccount
     * @param LoggerInterface $logger
     */
    public function __construct(Dispatcher $dispatcher, Account $nodeAccount, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->node = $nodeAccount;
        $this->logger = $logger;
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

        $this->logger->debug('Send message to : ' . json_encode($nodes));

        $this->dispatcher->queue($chain, $nodes);
    }
    
    /**
     * Get the node url that the dispatcher is running on
     * 
     * @return string
     */
    public function getNode()
    {
        return $this->dispatcher->getNode();
    }
}
