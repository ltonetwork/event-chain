<?php declare(strict_types=1);

namespace EventChainRebase;

use Event;
use InvalidArgumentException;

/**
 * Stitch two events when rebasing event chains
 */
class EventStitch
{
    /**
     * @var Account
     **/
    protected $node;

    /**
     * EventStitch constructor.
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
     * @param Event|null $chainEvent
     * @param Event|null $forkEvent 
     * @param string|null $previous 
     * @return Event
     */
    public function stitch(?Event $chainEvent, ?Event $forkEvent, ?string $previous): Event
    {
        if ($chainEvent === null && $forkEvent === null) {
            throw new InvalidArgumentException('Can not stitch two empty events');
        }

        if ($chainEvent === null) {
            $event = $this->rebase($forkEvent, $previous);
        } elseif ($forkEvent === null) {
            $event = $this->rebase($chainEvent, $previous);
        } else {
            $event = $this->stitchEvents($chainEvent, $forkEvent, $previous);                
        }

        return $event;
    }

    /**
     * Alias of `stitch()`
     *
     * @param Event|null $chainEvent
     * @param Event|null $forkEvent 
     * @param string|null $previous 
     * @return Event
     */
    final public function __invoke(?Event $chainEvent, ?Event $forkEvent, ?string $previous): Event
    {
        return $this->stitch($chainEvent, $forkEvent, $previous);
    }

    /**
     * Rebase event without stitching
     *
     * @param Event $event
     * @param string|null $previous 
     * @return Event
     */
    protected function rebaseEvent(Event $event, ?string $previous): Event
    {
        $event = clone $event;
        $event->setValues([
            'timestamp' => (new DateTime)->getTimestamp(),
            'previous' => $previous
        ]);

        $event->signWith($this->node);

        return $event;
    }

    /**
     * Stitch two events
     *
     * @param Event $chainEvent
     * @param Event $forkEvent 
     * @param string|null $previous
     * @return Event
     */
    protected function stitchEvents(Event $chainEvent, Event $forkEvent, ?string $previous): Event
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
     * @param string|null $previous 
     * @return array
     */
    protected function getStitchValues(Event $stitched, Event $original, ?string $previous): array
    {
        $values = array_only($stitched->getValues(), ['origin', 'body', 'receipt']);
        $values['original'] = array_only($original->getValues(), ['timestamp', 'previous', 'signkey', 'signature', 'hash', 'receipt']);
        $values['timestamp'] = (new DateTime)->getTimestamp();
        $values['previous'] = $previous;

        return $values;
    }
}
