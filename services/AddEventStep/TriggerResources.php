<?php declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use EventChain;
use Event;
use ResourceFactory;
use ResourceTrigger;

/**
 * Some services want to know if all new events of a chain have been processed. This is especially true for the
 * workflow engine, which now checks the state so it can perform a system action if required.
 */
class TriggerResources
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var ResourceTrigger
     */
    protected $resourceTrigger;


    /**
     * Class constructor.
     *
     * @param EventChain      $chain
     * @param \ResourceFactory $factory
     * @param \ResourceTrigger $resourceTrigger
     */
    public function __construct(EventChain $chain, ResourceFactory $factory, ResourceTrigger $resourceTrigger)
    {
        $this->chain = $chain;
        $this->resourceFactory = $factory;
        $this->resourceTrigger = $resourceTrigger;
    }

    /**
     * Invoke the step.
     *
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @param ArrayObject      $newEvents
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation, ArrayObject $newEvents): Pipeline
    {
        $resources = [];

        return $pipeline
            ->apply(function(Event $event) use ($validation, &$resources, $newEvents) {
                if ($validation->failed()) {
                    return;
                }

                $resources[] = $this->resourceFactory->extractFrom($event);

                if ($event !== $newEvents[count($newEvents) - 1]) {
                    return; // Don't don anything until the last event has been processed.
                }

                $newChain = $this->chain->withEvents($newEvents->getArrayCopy());
                $addedEvents = $this->resourceTrigger->trigger($resources, $newChain);

                foreach ($addedEvents->events ?? [] as $event) {
                    $newEvents->append($event);
                }
            });
    }
}
