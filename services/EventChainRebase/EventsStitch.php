<?php declare(strict_types=1);

namespace EventChainRebase;

use InvalidArgumentException;

/**
 * Stitch two events when rebasing event chains
 */
class EventsStitch
{
    /**
     * @var Account
     **/
    protected $node;

    /**
     * EventsStitch constructor.
     *
     * @param Account $node
     */
    public function __construct(Account $node)
    {
        $this->node = $node;
    }

    /**
     * Stitch two events
     *
     * @param Event|null $event1
     * @param Event|null $event2 
     * @param Event|null $previous 
     * @return Event
     */
    public function stitch(?Event $event1, ?Event $event2, ?Event $previous): Event
    {
        if ($event1 === null && $event2 === null) {
            throw new InvalidArgumentException('Can not stitch two empty events');
        }

        if ($event1 === null) {
            $event = $this->rebaseEvent($event2, $previous);
        } elseif ($event2 === null) {
            $event = $this->rebaseEvent($event1, $previous);
        } else {
            $event = $this->stitchEvents($event2, $event1, $previous);                
        }

        return $event;
    }

    /**
     * Alias of `stitch()`
     *
     * @param Event|null $event1
     * @param Event|null $event2 
     * @param Event|null $previous 
     * @return Event
     */
    final public function __invoke(?Event $event1, ?Event $event2, ?Event $previous): Event
    {
        return $this->stitch($event1, $event2, $previous);
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
        $event = clone $event;
        $event->setValues([
            'timestamp' => (new DateTime)->getTimestamp(),
            'previous' => $previous ? $previous->hash : null
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
        $values['previous'] = $previous ? $previous->hash : null;

        return $values;
    }
}
