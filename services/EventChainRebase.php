<?php declare(strict_types=1);

use LTO\Account;
use Improved\Iterator\CombineIterator;
use Improved\IteratorPipeline\Pipeline;

/**
 * Service to rebase a fork of an event chain upon the leading chain.
 */
class EventChainRebase
{
    /**
     * @var EventChainGateway
     */
    protected $gateway;

    /**
     * @var Account
     **/
    protected $node;

    /**
     * EventChainRebase constructor.
     *
     * @param EventChainGateway $gateway
     */
    public function __construct(EventChainGateway $gateway, Account $node)
    {
        $this->node = $node;
        $this->gateway = $gateway;
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

            if ($forkEvent === null) {
                $event = $this->rebaseEvent($chainEvent, $previous);
            } elseif ($chainEvent === null) {
                $event = $this->rebaseEvent($forkEvent, $previous);
            } else {
                $event = $this->stitchEvents($chainEvent, $forkEvent, $previous);                
            }

            $events[] = $event;
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
        $chainEvents = $chain->events;
        $forkEvents = $fork->events;

        if (count($chainEvents) > count($forkEvents)) {
            $forkEvents = array_pad($forkEvents, count($chainEvents), null);
        } else {
            $chainEvents = array_pad($chainEvents, count($forkEvents), null);
        }
        
        return Pipeline::with(new CombineIterator($chainEvents, $forkEvents));
    }

    /**
     * Rebase event without stitching
     *
     * @param Event $event
     * @param Event|null $previous 
     * @return Event
     */
    protected function rebaseEvent(Event $event, ?Event $previous): Event
    {
        $event->setValues([
            'timestamp' => (new DateTime)->getTimestamp(),
            'previous' => $previous ? $previous->getHash() : null
        ]);

        $event->signWith($this->node);

        return $event;
    }

    /**
     * Stitch two events
     *
     * @param Event $chainEvent
     * @param Event $forkEvent 
     * @param Event $previous
     * @return Event
     */
    protected function stitchEvents(Event $chainEvent, Event $forkEvent, ?Event $previous): Event
    {        
        if ($forkEvent->timestamp > $chainEvent->timestamp) {
            $original = $chainEvent;
            $stitched = $forkEvent;
        } else {
            $original = $forkEvent;
            $stitched = $chainEvent;                
        }

        $values = $this->getStitchValues($stitched, $original, $previous);        

        $event = new Event();
        $event->setValues($values);
        $event->signWith($this->node);

        return $event;
    }

    /**
     * Get values to create stitched event
     *
     * @param Event $stitched
     * @param Event $original 
     * @param Event|null $previous 
     * @return array
     */
    protected function getStitchValues(Event $stitched, Event $original, ?Event $previous): array
    {
        $values = array_only($stitched->getValues(), ['origin', 'body', 'receipt']);
        $values['original'] = array_only($original->getValues(), ['timestamp', 'previous', 'signkey', 'signature', 'hash', 'receipt']);
        $values['timestamp'] = (new DateTime)->getTimestamp();
        $values['previous'] = $previous ? $previous->getHash() : null;

        return $values;
    }
}
