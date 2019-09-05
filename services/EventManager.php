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
     * @var ResourceTrigger
     */
    protected $resourceTrigger;
    
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
        ResourceTrigger $resourceTrigger,
        DispatcherManager $dispatcher,
        EventFactory $eventFactory,
        AnchorClient $anchor,
        EventChainGateway $chainGateway,
        ConflictResolver $conflictResolver
    ) {
        $node = $eventFactory->getNodeAccount();
        object_set_dependencies($this, get_defined_vars());
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
        if ($chain->id !== $newEvents->id) {
            throw new UnexpectedValueException("Can't add events of a different chain");
        }

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
            new Step\SyncChains($chain),
            new Step\SkipKnownEvents(),
            new Step\HandleFork($chain, $this->conflictResolver),
            new Step\ValidateNewEvent($chain),
            new Step\StoreResource($chain, $this->resourceFactory, $this->resourceStorage),
            new Step\HandleFailed($chain, $this->eventFactory),
            new Step\SaveEvent($chain, $this->chainGateway),
            new Step\AnchorEvent($chain, $this->node, $this->anchor),
            new Step\TriggerResources($chain, $this->resourceFactory, $this->resourceTrigger),
            new Step\Walk($chain), // <-- Nothing will happen without this step
            new Step\Dispatch($chain, $this->dispatcher, $this->node, $chain->getNodes()),
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
        $validation = $newEvents->validate();

        // Using an ArrayObject, so more events can be added while iterating through the new events.
        $eventList = new ArrayObject($newEvents->events->getArrayCopy());
        $data = $eventList;

        foreach ($steps as $step) {
            $data = $step($data, $validation, $eventList);
        }

        return $validation;
    }
}
