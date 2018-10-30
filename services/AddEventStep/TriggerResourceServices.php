<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use LTO\Account;
use ResourceStorage;
use Jasny\ValidationResult;

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
     * @var ResourceStorage
     */
    protected $resourceStorage;

    /**
     * @var Account
     */
    protected $node;


    /**
     * Class constructor.
     *
     * @param EventChain $chain
     * @param ResourceStorage $storage
     */
    public function __construct(EventChain $chain, ResourceStorage $storage, Account $node)
    {
        $this->chain = $chain;
        $this->resourceStorage = $storage;
        $this->node = $node;
    }

    /**
     * Invoke the step.
     *
     * @param EventChain       $partial
     * @param ValidationResult $validation
     * @return EventChain
     */
    public function __invoke(EventChain $partial, ValidationResult $validation): EventChain
    {
        $signal =
            $validation->succeeded() &&
            $partial->events !== [] &&
            $this->chain->isEventSignedByAccount($partial->getLastEvent(), $this->node);

        if ($signal) {
            $this->resourceStorage->done($this->chain);
        }

        return $partial;
    }
}