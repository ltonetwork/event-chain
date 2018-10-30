<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use Improved\IteratorPipeline\Pipeline;

/**
 * The previous actions are just setting up the pipeline, but nothing actually happends until we walk through the
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
        $events = $pipeline->toArray();

        return $this->chain->withEvents($events);
    }
}
