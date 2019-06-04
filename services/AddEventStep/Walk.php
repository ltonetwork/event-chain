<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use Improved\IteratorPipeline\Pipeline;

/**
 * The previous actions are just setting up the pipeline, but nothing actually happens until we walk through the
 * events.
 */
class Walk
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * Dispatch constructor.
     *
     * @param EventChain $chain
     */
    public function __construct(EventChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Invoke the step
     *
     * @param Pipeline $pipeline
     * @return EventChain
     */
    public function __invoke(Pipeline $pipeline): EventChain
    {
        $newEvents = $this->chain->withoutEvents();

        foreach ($pipeline as $event) {
            $this->chain->events[] = $event;
            $newEvents->events[] = $event;
        }

        return $newEvents;
    }
}
