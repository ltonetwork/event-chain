<?php declare(strict_types=1);

namespace AddEventStep;

use Improved as i;
use Jasny\ValidationResult;
use LTO\Account;

/**
 * Some services want to know if all new events of a chain have been processed. This is especially true for the
 * workflow engine, which now checks the state so it can perform a system action if required.
 */
class TriggerResourceServices
{
    /**
     * @var \EventChain
     */
    protected $chain;

    /**
     * @var \ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \ResourceStorage
     */
    protected $resourceStorage;

    /**
     * @var Account
     */
    protected $node;


    /**
     * Class constructor.
     *
     * @param \EventChain      $chain
     * @param \ResourceFactory $factory
     * @param \ResourceStorage $storage
     * @param Account          $node
     */
    public function __construct(\EventChain $chain, \ResourceFactory $factory, \ResourceStorage $storage, Account $node)
    {
        $this->chain = $chain;
        $this->resourceFactory = $factory;
        $this->resourceStorage = $storage;
        $this->node = $node;
    }

    /**
     * Invoke the step.
     *
     * @param \EventChain      $partial
     * @param ValidationResult $validation
     * @return \EventChain
     */
    public function __invoke(\EventChain $partial, ValidationResult $validation): \EventChain
    {
        $signal =
            $validation->succeeded() &&
            $partial->events !== [] &&
            $this->chain->isEventSignedByAccount($partial->getLastEvent(), $this->node);

        if ($signal) {
            $this->signalResources($partial);
        }

        return $partial;
    }

    /**
     * Signal the resources that we're done adding events.
     *
     * @param \EventChain $partial
     */
    protected function signalResources(\EventChain $partial): void
    {
        $resources = i\iterable_map($partial->events, [$this->resourceFactory, 'extractFrom']);
        $this->resourceStorage->done($resources, $this->chain);
    }
}
