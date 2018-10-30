<?php declare(strict_types=1);

namespace AddEventStep;

use Event;
use EventChain;
use Improved\IteratorPipeline\Pipeline;

/**
 * Everything has checked out and the event has been processed. It is added to the event chain and stored to the DB.
 */
class SaveEvent
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * SaveEvent constructor.
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
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline): Pipeline
    {
        return $pipeline->apply(function(Event $event) {
            $this->chain->events->add($event);

            // TODO: Use a service for this. Active records suck.
            $this->chain->save();
        });
    }
}
