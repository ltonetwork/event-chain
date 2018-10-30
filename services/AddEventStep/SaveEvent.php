<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

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
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation): Pipeline
    {
        return $pipeline->apply(function(Event $event) use ($validation) {
            $this->chain->events->add($event);

            // TODO: Use a service for this. Active records suck.
            $this->chain->save();
        });
    }
}
