<?php declare(strict_types=1);

use LTO\Account;
use Jasny\DB\EntitySet;
use Improved\Iterator\CombineIterator;
use Improved\IteratorPipeline\Pipeline;
use EventChainRebase\EventStitch;

/**
 * Service to rebase a fork of an event chain upon the leading chain.
 */
class EventChainRebase
{
    /**
     * @var Account
     **/
    protected $node;

    /**
     * Pbject to perform events stitching
     * @var EventStitch
     **/
    protected $stitcher;

    /**
     * EventChainRebase constructor.
     *
     * @param Account $node
     */
    public function __construct(Account $node, EventStitch $stitcher)
    {
        $this->node = $node;
        $this->stitcher = $stitcher;
    }

    /**
     * Rebase fork onto chain.
     *
     * @param EventChain $chain
     * @param EventChain $fork
     * @return EventChain
     */
    public function rebase(EventChain $chain, EventChain $fork): EventChain
    {
        $events = [];
        $pipe = $this->mapBranches($chain, $fork);

        $pipe->apply(function(?Event $forkEvent, ?Event $chainEvent) use ($chain, &$events) {
            $previous = $this->getPreviousHash($chain, $events);
            $events[] = ($this->stitcher)($chainEvent, $forkEvent, $previous);
        })->walk();

        $chain = (new EventChain())->withEvents($events);

        return $chain;
    }

    /**
     * Alias of `rebase()`
     *
     * @param EventChain $chain
     * @param EventChain $fork
     * @return EventChain
     */
    final public function __invoke(EventChain $chain, EventChain $fork): EventChain
    {
        return $this->rebase($chain, $fork);
    }

    /**
     * Represent branches as iterator
     *
     * @param EventChain $chain
     * @param EventChain $fork 
     * @return Pipeline
     */
    protected function mapBranches(EventChain $chain, EventChain $fork): Pipeline
    {
        $chainEvents = $chain->events instanceof EntitySet ? $chain->events->getArrayCopy() : $chain->events;
        $forkEvents = $fork->events instanceof EntitySet ? $fork->events->getArrayCopy() : $fork->events;

        if (count($chainEvents) > count($forkEvents)) {
            $forkEvents = array_pad($forkEvents, count($chainEvents), null);
        } else {
            $chainEvents = array_pad($chainEvents, count($forkEvents), null);
        }
        
        return Pipeline::with(new CombineIterator($chainEvents, $forkEvents));
    }

    /**
     * Get previous event hash for current processing event
     *
     * @param EventChain $chain
     * @param array $events 
     * @return string|null
     */
    protected function getPreviousHash(EventChain $chain, array $events): ?string
    {
        if (count($events) === 0) {
            $first = $chain->getFirstEvent();

            return $first->previous;
        }

        $last = end($events);

        return $last->hash;
    }
}
