<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Jasny\ValidationResult;
use LTO\Account;
use EventChain;

/**
 * Some services want to know if all new events of a chain have been processed. This is especially true for the
 * workflow engine, which now checks the state so it can perform a system action if required.
 */
class TriggerResourceServices
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var \ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \ResourceTrigger
     */
    protected $resourceTrigger;

    /**
     * @var Account
     */
    protected $node;


    /**
     * Class constructor.
     *
     * @param EventChain      $chain
     * @param \ResourceFactory $factory
     * @param \ResourceTrigger $resourceTrigger
     * @param Account          $node
     */
    public function __construct(EventChain $chain, \ResourceFactory $factory, \ResourceTrigger $resourceTrigger, Account $node)
    {
        $this->chain = $chain;
        $this->resourceFactory = $factory;
        $this->resourceTrigger = $resourceTrigger;
        $this->node = $node;
    }

    /**
     * Invoke the step.
     *
     * @param EventChain      $partial
     * @param ValidationResult $validation
     * @return EventChain|null               Events created after triggering some workflow actions
     */
    public function __invoke(EventChain $partial, ValidationResult $validation): ?EventChain
    {
        $signal =
            $validation->succeeded() &&
            count($partial->events) !== 0 &&
            $this->chain->isEventSignedByAccount($partial->getLastEvent(), $this->node);

        $newEvents = $signal ? $this->signalResources($partial) : null;

        return $newEvents;
    }

    /**
     * Signal the resources that we're done adding events.
     *
     * @param EventChain $partial
     * @return EventChain|null      Events created after triggering some workflow actions
     */
    protected function signalResources(EventChain $partial): ?EventChain
    {
        $resources = i\iterable_map($partial->events, [$this->resourceFactory, 'extractFrom']);

        return $this->resourceTrigger->trigger($resources, $this->chain);
    }
}
