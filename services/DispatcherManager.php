<?php declare(strict_types=1);

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
     * @param Dispatcher      $dispatcher
     * @param Account         $nodeAccount
     * @param LoggerInterface $logger
     */
    public function __construct(Dispatcher $dispatcher, Account $nodeAccount, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->node = $nodeAccount;
        $this->logger = $logger;
    }


    /**
     * Send the event chain to the own dispatcher service
     *
     * @param EventChain $chain
     */
    public function queueToSelf(EventChain $chain): void
    {
        $this->dispatcher->queue($chain);
    }
    
    
    /**
     * Send the event chain to the dispatcher service
     * 
     * @param EventChain    $chain
     * @param string[]|null $nodes
     */
    public function dispatch(EventChain $chain, ?array $nodes = null): void
    {
        $event = $chain->getLastEvent();
        
        if ($event->signkey === null || !$chain->isEventSignedByAccount($event, $this->node)) {
            return;
        }

        $to = isset($nodes) ? json_encode($nodes) : 'local node';
        $this->logger->debug("dispatcher: send message to $to");

        $this->dispatcher->queue($chain, $nodes);
    }
    
    /**
     * Get the node url that the dispatcher is running on
     * 
     * @return string
     */
    public function getNode(): string
    {
        return $this->dispatcher->getNode();
    }
}
