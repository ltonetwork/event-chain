<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * If a fork is detected it's resolved using the `ConflictResolver` service. From the forked event, the new rebased
 * events are yielded.
 */
class HandleFork
{
    /**
     * @var \EventChain
     */
    protected $chain;

    /**
     * @var \ConflictResolver
     */
    protected $conflictResolver;

    /**
     * Class constructor.
     *
     * @param \EventChain       $chain
     * @param \ConflictResolver $conflictResolver
     */
    public function __construct(\EventChain $chain, \ConflictResolver $conflictResolver)
    {
        $this->chain = $chain;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * Invoke the step.
     *
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation): Pipeline
    {
        return $pipeline->then(function (iterable $events) {
            return $this->iterate($events);
        });
    }

    /**
     * Iterate through the events OR rebased events in case of a fork.
     *
     * {@internal We only need to look at the first event to see if there is a fork. However we don't want to just get
     *   that event and return the rebased events as alt iterable. This would mess up the iteration over all the steps.
     *   Instead we always return the same Generator, which will determine what to yield when invoked for the first
     *   time.}}
     *
     * @param iterable|\Event[] $events
     * @return \Generator
     */
    protected function iterate(iterable $events): \Generator
    {
        error_log('STEP III, HANDLE FORK');
        $forked = false;
        $forkedEvents = [];

        foreach ($events as $known => $new) {
            i\type_check($known, [\Event::class, 'null']);
            i\type_check($new, \Event::class);

            $forked = $forked || ($known !== null && $known->hash !== $new->hash);

            if ($forked) {
                $forkedEvents[] = $new;
                continue;
            }

            yield $new;
        }

        error_log('GATHERED FORKED EVENTS: ');
        debug_events($forkedEvents, true);

        if ($forked) {
            $rebasedChain = $this->resolveConflict($forkedEvents);

            foreach ($rebasedChain->events as $rebasedEvent) {
                error_log('YIELD REBASED EVENT: ' . $rebasedEvent->hash);
                yield $rebasedEvent;
            }
        }
    }

    /**
     * Resolve a conflict and return rebased events.
     *
     * @param \Event[] $forked
     * @return \EventChain
     */
    protected function resolveConflict(array $forked): \EventChain
    {
        $forkPoint = i\iterable_first($forked)->previous;

        $ourChain = $this->chain->getPartialAfter($forkPoint);
        $theirChain = $this->chain->withEvents($forked);

        return $this->conflictResolver->handleFork($ourChain, $theirChain);
    }
}
