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
     * Class constructor
     *
     * @param ResourceFactory   $resourceFactory
     * @param ResourceStorage   $resourceStorage
     * @param DispatcherManager $dispatcher
     * @param EventFactory      $eventFactory
     * @param AnchorClient      $anchor
     */
    public function __construct(
        ResourceFactory $resourceFactory,
        ResourceStorage $resourceStorage,
        DispatcherManager $dispatcher,
        EventFactory $eventFactory,
        AnchorClient $anchor
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceStorage = $resourceStorage;
        $this->dispatcher = $dispatcher;
        $this->eventFactory = $eventFactory;
        $this->node = $eventFactory->getNodeAccount();
        $this->anchor = $anchor;
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
     * Add new events
     *
     * @param EventChain $newEvents
     * @return ValidationResult
     */
    public function add(EventChain $newEvents): ValidationResult
    {
        $this->assertChain();

        return $this->step(
            $newEvents,
            new Step\ValidateInput($this->chain),
            new Step\SyncChains($this->chain),
            new Step\SkipKnownEvents($this->eventFactory),
            new Step\ValidateNewEvent($this->chain),
            new Step\StoreResource($this->chain, $this->resourceFactory, $this->resourceStorage),
            new Step\HandleFailed($this->chain, $this->eventFactory),
            new Step\SaveEvent($this->chain),
            new Step\Walk($this->chain), // <-- Nothing will happen without this step
            new Step\Dispatch($this->chain, $this->dispatcher, $this->chain->getNodes()),
            new Step\TriggerResourceServices()
        );
    }
}
