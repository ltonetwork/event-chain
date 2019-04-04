<?php declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;

/**
 * Resolve a conflict when a fork is detected.
 *
 * Get the anchor transaction of the two events where the chains fork. If our event was anchored earlier, do nothing.
 * If the other event was anchored first, our state is no good. We need to rebase our fork onto the other chain. Then
 * we delete and rebuild all projected data (like the process).
 */
class ConflictResolver
{
    /**
     * @var AnchorClient
     */
    protected $anchor;

    /**
     * @var EventChainRebase
     */
    protected $rebaser;

    /**
     * Class constructor.
     *
     * @param AnchorClient     $anchor
     * @parma EventChainRebase $rebaser
     */
    public function __construct(AnchorClient $anchor, EventChainRebase $rebaser)
    {
        $this->anchor = $anchor;
        $this->rebaser = $rebaser;
    }

    /**
     * Invoke the resolver
     *
     * @param EventChain $ourChain
     * @param EventChain $theirChain
     * @return EventChain
     * @throws UnresolvableConflictException
     */
    public function handleFork(EventChain $ourChain, EventChain $theirChain): EventChain
    {
        $ourEvent = $ourChain->getFirstEvent();
        $theirEvent = $theirChain->getFirstEvent();

        if ($ourEvent->hash === $theirEvent->hash) {
            $hash = $ourEvent->hash;
            throw new InvalidArgumentException("First event of partial chains should differ, both are '$hash'");
        }

        if ($this->getEarliestEvent($ourEvent, $theirEvent) === $ourEvent) {
            return $ourChain->withEvents([]);
        }

        $mergedChain = $this->rebaser->rebase($theirChain, $ourChain);

        return $mergedChain;
    }

    /**
     * Get the event that was anchored first.
     * First compare block heights and both are in the same block, the transaction position within a block.
     *
     * @param Event ...$events
     * @return Event
     * @throws UnresolvableConflictException
     */
    protected function getEarliestEvent(Event ...$events): Event
    {
        $map = [];
        foreach ($events as $event) {
            $map[$event->hash] = $event;
        }

        try {
            return Pipeline::with($map)
                ->flip()
                ->then([$this->anchor, 'fetchMultiple']) // Loops through all hashes and returns a new iterator.
                ->sort(function (stdClass $info1, stdClass $info2) {
                    return (int)version_compare(
                        "{$info1->block->height}.{$info1->transaction->position}",
                        "{$info2->block->height}.{$info2->transaction->position}"
                    );
                })
                ->mapKeys(function ($_, string $hash) use ($map) {
                    return $map[$hash];
                })
                ->flip()
                ->first(true);
        } catch (RangeException $e) {
            throw $this->notAnchoredException($map);
        } catch (Exception $exception) {
            throw new UnresolvableConflictException("Failed to fetch from anchoring service", 0, $exception);
        }
    }

    /**
     * Create an unresolvable conflict exception when both chains are not anchored.
     *
     * @param Event[] $eventsMap
     * @return UnresolvableConflictException
     */
    protected function notAnchoredException(array $eventsMap)
    {
        $hashes = array_keys($eventsMap);

        return new UnresolvableConflictException(
            sprintf("Events '%s' are not anchored yet", join(', ', $hashes)),
            UnresolvableConflictException::NOT_ANCHORED
        );
    }
}
