<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use EventFactory;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * Once the validation object has an error, subsequent events will no longer be processed. This step collects all events
 * after a failure occurs and uses them to create an error event. This error event is added to the chain.
 */
class HandleFailed
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var EventFactory
     */
    protected $eventFactory;


    /**
     * HandleFailed constructor.
     *
     * @param EventChain   $chain
     * @param EventFactory $factory
     */
    public function __construct(EventChain $chain, EventFactory $factory)
    {
        $this->chain = $chain;
        $this->eventFactory = $factory;
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
        return $pipeline->then(function(iterable $events) use ($validation): \Generator {
            $failed = [];

            foreach ($events as $event) {
                if ($validation->succeeded()) {
                    yield $event;
                } else {
                    $failed[] = $event;
                }
            }

            if ($failed !== []) {
                yield $this->eventFactory->createErrorEvent($validation->getErrors(), $failed);
            }
        });
    }
}
