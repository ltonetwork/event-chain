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
        $this->resourceFactory = $resourceFactory;
        $this->resourceStorage = $resourceStorage;
        $this->dispatcher = $dispatcher;
        $this->eventFactory = $eventFactory;
        $this->node = $eventFactory->getNodeAccount();
        $this->anchor = $anchor;
        $this->chainGateway = $chainGateway;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * Add new events to an event chain.
     *
     * @param EventChain $chain
     * @param EventChain $newEvents
     * @return ValidationResult
     */
    public function add(EventChain $chain, EventChain $newEvents): ValidationResult
    {
        if ($chain->isPartial()) {
            throw new UnexpectedValueException("Partial event chain; doesn't contain the genesis event");
        }

        $steps = $this->getSteps($chain);
        
        return $this->step($newEvents, ...$steps);
    }

    /**
     * Get all steps to process event.
     *
     * @param EventChain $chain
     * @return callable[]
     */
    protected function getSteps(EventChain $chain): array
    {
        return [
            new Step\ValidateInput($chain),
            new Step\SyncChains($chain),
            new Step\SkipKnownEvents(),
            new Step\HandleFork($chain, $this->conflictResolver),
            new Step\ValidateNewEvent($chain),
            new Step\StoreResource($chain, $this->resourceFactory, $this->resourceStorage),
            new Step\HandleFailed($chain, $this->eventFactory),
            new Step\SaveEvent($chain, $this->chainGateway),
            new Step\Walk($chain), // <-- Nothing will happen without this step
            new Step\Dispatch($chain, $this->dispatcher, $this->node, $chain->getNodes()),
            new Step\TriggerResourceServices($chain, $this->resourceFactory, $this->resourceStorage, $this->node)
        ];
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
}
