<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use Improved\Iterator\CombineIterator;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * The new events are probably a partial chain. We step over the events of the chain in order to find the position
 * where the submitted events should go. This doesn't have to be at the end of the chain, as some (but not all) of the
 * new events are already known and processed. Submitting events is idempotent.
 */
class SyncChains
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * Class constructor.
     *
     * @param EventChain $chain
     */
    public function __construct(EventChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Invoke the step.
     *
     * @param EventChain       $newEvents
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(EventChain $newEvents, ValidationResult $validation): Pipeline
    {
        $following = [];
        $previous = $newEvents->getFirstEvent()->previous;

        try {
            $following = $this->chain->getEventsAfter($previous);
        } catch (\OutOfBoundsException $e) {
            $validation->addError("events don't fit on chain, '%s' not found", $previous);
        }

        $known = array_values($following) + array_fill(null, 0, count($newEvents->events));

        return Pipeline::with(new CombineIterator($known, $newEvents->events));
    }
}
