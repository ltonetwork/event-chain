<?php declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use EventChain;
use FillCombineIterator;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * The new events are probably a partial chain. We step over the events of the chain in order to find the position
 * where the submitted events should go. This doesn't have to be at the end of the chain, as some (but not all) of the
 * new events are already known and processed. Submitting events is idempotent.
 *
 * If the full chain is `A-B-C-D-E` and you receive a partial event chain `D-E-F`, then this service will forward `D`,
 * creating a iterator with the following pairs `D/D - E/E - null/F`.
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
     * @param ArrayObject      $newEvents   Using an ArrayObject, so more events can be added while stepping.
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(ArrayObject $newEvents, ValidationResult $validation): Pipeline
    {
        error_log('STEP I, SYNC CHAINS');
        
        $following = [];
        $previous = $newEvents[0]->previous;

        try {
            $following = $this->chain->getEventsAfter($previous);
        } catch (\OutOfBoundsException $e) {
            $validation->addError("events don't fit on chain, '%s' not found", $previous);
        }

        return Pipeline::with(new FillCombineIterator(array_values($following), $newEvents));
    }
}
