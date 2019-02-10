<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * Some (but not all) of the new events may already known and processed. Submitting events is idempotent. We skip
 * through these events.
 *
 * If a fork is detected it's resolved using the `ConflictResolver` service. From to forked event, the new rebased
 * events are yielded.
 */
class DetermineNewEvents
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
        return $pipeline->then([$this, 'iterate']);
    }

    /**
     * Iterate through all events.
     *
     * @param iterable $events
     * @return \Generator
     */
    protected function iterate(iterable $events): \Generator
    {
        $forked = null;

        foreach ($events as $known => $new) {
            type_check($new, \Event::class);
            type_check($known, [\Event::class, 'null']);

            if ($known !== null && $forked === null && $known->hash !== $new->hash) {
                $msg = "fork detected in chain '%s'; conflict on '%s' and '%s'";
                trigger_error(sprintf($msg, $this->chain->id, $new->hash, $known->hash), \E_USER_NOTICE);
                $forked = [];
            }

            if ($forked !== null) {
                $forked[] = $new;
                continue;
            }

            if ($known === null) {
                yield $new;
            }
        }

        if ($forked !== null) {
            $rebasedChain = $this->conflictResolver($forked);

            foreach ($rebasedChain->events as $rebasedEvent) {
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
    protected function resolveConflict(array $forked)
    {
        $forkPoint = i\iterable_first($forked)->previous;

        $ourChain = $this->chain->getEventsAfter($forkPoint);
        $theirChain = $this->chain->withEvents($forked);

        return $this->conflictResolver->handleFork($ourChain, $theirChain);
    }
}
