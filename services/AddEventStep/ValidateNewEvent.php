<?php declare(strict_types=1);

namespace AddEventStep;

use Event;
use EventChain;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * A more extensive validation for each new event. This includes checking signatures.
 */
class ValidateNewEvent
{
    /**
     * @var EventChain
     */
    protected $chain;


    /**
     * ValidateNewEvent constructor.
     *
     * @param EventChain $chain
     */
    public function __construct(EventChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Invoke the step.
     * If a fork is detected, we loop straight through the new events and yield a single error event.
     *
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation): Pipeline
    {
        return $pipeline->apply(function (Event $event) use ($validation): void {
            if ($validation->failed()) {
                return;
            }

            $validation->add($event->validate(), "event '$event->hash': ");

            if ($event->previous !== $this->chain->getLatestHash()) {
                $validation->addError("event '%s' doesn't fit on chain", $event->hash);
            }
        });
    }
}
