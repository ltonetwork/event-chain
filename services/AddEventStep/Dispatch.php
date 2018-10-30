<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;

class Dispatch
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var string[]
     */
    protected $oldNodes;

    /**
     * Dispatch constructor.
     *
     * @param EventChain $chain
     * @param string[]   $oldNodes
     */
    public function __construct(EventChain $chain, array $oldNodes = [])
    {
        $this->chain = $chain;
        $this->oldNodes = $oldNodes;
    }

    /**
     * Invoke this step
     *
     * @param EventChain $events
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
