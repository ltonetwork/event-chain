<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use DispatcherManager;
use LTO\Account;

/**
 * The dispatcher service sends new events to nodes of the other participants. If a participant has used our node to
 * add events, it's up to us to send it to the other nodes.
 *
 * An identity event can add a new participant with possibly a new node, or change the node of an existing
 * participant. In that case we send the whole event chain to that node.
 */
class Dispatch
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var DispatcherManager
     */
    protected $dispatcher;

    /**
     * @var Account
     */
    protected $node;

    /**
     * @var string[]
     */
    protected $oldNodes;


    /**
     * Dispatch constructor.
     *
     * @param EventChain        $chain
     * @param DispatcherManager $dispatcher
     * @param Account           $node
     * @param string[]          $oldNodes     URLs of the nodes that are on the chain before processing new events.
     */
    public function __construct(EventChain $chain, DispatcherManager $dispatcher, Account $node, array $oldNodes = [])
    {
        $this->chain = $chain;
        $this->dispatcher = $dispatcher;
        $this->node = $node;
        $this->oldNodes = $oldNodes;
    }

    /**
     * Invoke this step
     *
     * @param EventChain $partial
     * @return EventChain
     */
    public function __invoke(EventChain $partial): EventChain
    {
        $this->dispatch($partial);

        return $partial;
    }

    /**
     * Send a partial or full chain to the event dispatcher service
     *
     * @param EventChain $partial
     * @return void
     */
    protected function dispatch(EventChain $partial): void
    {
        $systemNodes = $this->chain->getNodesForSystem($this->node->getPublicSignKey());
        $otherNodes = $this->getAllNodesExcept($this->oldNodes, $systemNodes);

        // send partial chain to old nodes
        if ($partial->events !== [] && $otherNodes !== []) {
            $this->dispatcher->dispatch($partial, $otherNodes);
        }

        // send full node to new nodes
        $newNodes = $this->getAllNodesExcept($this->chain->getNodes(), $this->oldNodes, $systemNodes);

        if ($newNodes !== []) {
            $this->dispatcher->dispatch($this->chain, $newNodes);
        }
    }

    /**
     * Get all nodes, except thoses specified.
     *
     * @param array $nodes
     * @param array ...$except
     * @return array
     */
    protected function getAllNodesExcept(array $nodes, array ...$except)
    {
        return array_unique(array_values(array_diff($nodes, ...$except)));
    }
}
