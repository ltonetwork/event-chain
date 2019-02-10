<?php declare(strict_types=1);

use AddEventStep as Step;
use Jasny\ValidationResult;
use LTO\Account;

/**
 * Service to handle new events.
 */
class EventManager
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
     * @var ResourceStorage
     */
    protected $resourceStorage;
    
    /**
     * @var DispatcherManager
     */
    protected $dispatcher;
    
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var Account
     */
    protected $node;
    
    /**
     * @var AnchorClient
     */
    protected $anchor;

    /**
     * @var EventChainGateway
     */
    protected $chainGateway;

    /**
     * @var ConflictResolver
     */
    protected $conflictResolver;


    /**
     * Class constructor
     */
    public function __construct(
        ResourceFactory $resourceFactory,
        ResourceStorage $resourceStorage,
        DispatcherManager $dispatcher,
        EventFactory $eventFactory,
        AnchorClient $anchor,
        EventChainGateway $chainGateway,
        ConflictResolver $conflictResolver
    ) {
        foreach (func_get_args() as $prop => $service) {
            $this->prop = $service;
        }

        $this->node = $eventFactory->getNodeAccount();
    }

    /**
     * Create a manager for specified chain.
     *
     * @param EventChain $chain
     * @return static
     * @throws UnexpectedValueException for a partial chain
     */
    public function with(EventChain $chain): self
    {
        if (isset($this->chain)) {
            throw new BadMethodCallException("Chain already set");
        }

        if ($chain->isPartial()) {
            throw new UnexpectedValueException("Event chain doesn't contain the genesis event");
        }

        $clone = clone $this;
        $clone->chain = $chain;

        return $clone;
    }


    /**
     * Assert that a chain has been set
     *
     * @return void
     * @throw BadMethodCallException if no chain is set.
     */
    protected function assertChain(): void
    {
        if (!isset($this->chain)) {
            throw new BadMethodCallException("Chain not set; use the `withChain()` method.");
        }
    }

    /**
     * Walk through all the steps of adding an event.
     *
     * @param EventChain $newEvents
     * @param callable   ...$steps
     * @return ValidationResult
     */
    protected function step(EventChain $newEvents, callable ...$steps): ValidationResult
    {
        $validation = new ValidationResult();
        $data = $newEvents;

        foreach ($steps as $step) {
            $data = $step($data, $validation);
        }

        return $validation;
    }

    /**
     * Add new events to an event chain.
     *
     * @param EventChain $newEvents
     * @return ValidationResult
     */
    public function add(EventChain $newEvents): ValidationResult
    {
        $this->assertChain();

        $steps = $this->getSteps();
        
        return $this->step($newEvents, ...$steps);
    }

    /**
     * Get all steps to process event
     *
     * @codeCoverageIgnore
     * @return array
     */
    protected function getSteps(): array
    {
        return [
            new Step\ValidateInput($this->chain),
            new Step\SyncChains($this->chain),
            new Step\DetermineNewEvents($this->chain, $this->conflictResolver),
            new Step\ValidateNewEvent($this->chain),
            new Step\StoreResource($this->chain, $this->resourceFactory, $this->resourceStorage),
            new Step\HandleFailed($this->chain, $this->eventFactory),
            new Step\SaveEvent($this->chain, $this->chainGateway),
            new Step\Walk($this->chain), // <-- Nothing will happen without this step
            new Step\Dispatch($this->chain, $this->dispatcher, $this->node, $this->chain->getNodes()),
            new Step\TriggerResourceServices($this->chain, $this->resourceFactory, $this->resourceStorage, $this->node)
        ];
    }
}
