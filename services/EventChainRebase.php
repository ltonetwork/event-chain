<?php declare(strict_types=1);

use LTO\Account;
use Jasny\DB\EntitySet;
use Improved\Iterator\CombineIterator;
use Improved\IteratorPipeline\Pipeline;

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
     * @var EventsStitch
     **/
    protected $stitcher;

    /**
     * EventChainRebase constructor.
     *
     * @param Account $node
     */
    public function __construct(Account $node, EventsStitch $stitcher)
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

        $pipe->apply(function(?Event $forkEvent, ?Event $chainEvent) use ($events) {
            $previous = count($events) === 0 ? $chain->getFirstEvent() : end($events);

            $events[] = $this->stitcher($chainEvent, $forkEvent, $previous);
        });

        $chain = (new EventChain())->with($events);

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
}
