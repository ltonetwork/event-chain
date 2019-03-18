<?php declare(strict_types=1);

/**
 * Controller for processing new events.
 */
class EventController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * Data gateway to fetch/save event chains from/to the database.
     * @var EventChainGateway
     */
    protected $eventChains;

    /**
     * Interact with (external) event dispatcher service.
     * @var DispatcherManager
     */
    protected $dispatcher;

    /**
     * Handle new events.
     * @var EventManager
     */
    protected $manager;


    /**
     * Class constructor
     *
     * @param EventChainGateway $eventChainGateway  "models.event_chains"
     * @param DispatcherManager $dispatcher
     * @param EventManager      $eventManager
     */
    public function __construct(Gateway $eventChainGateway, DispatcherManager $dispatcher, EventManager $eventManager)
    {
        $this->eventChains = $eventChainGateway;
        $this->dispatcher = $dispatcher;
        $this->manager = $eventManager;

        $this->byDefaultSerializeTo('json');
    }


    /**
     * Add the chain to the queue.
     */
    public function queueAction(): void
    {
        $newChain = $this->createChainFromInput();

        if (!isset($newChain)) {
            return; // Bad request
        }

        $chain = $this->eventChains->fetch($newChain->id) ?: $newChain->withoutEvents();

        if (!$this->assertSignedByUs($newChain, $chain, $this->dispatcher->getNode())) {
            return; // Forbidden
        }

        // @todo: Add checks from $manager->add() here as well. Now the request is accepted and fails when processing.

        $this->dispatcher->queueToSelf($newChain);

        $this->noContent();
    }

    /**
     * Add a new chain or new events to an existing chain.
     */
    public function processAction(): void
    {
        $newChain = $this->createChainFromInput();

        if (!isset($newChain)) {
            return; // Bad request
        }

        $chain = EventChain::fetch($newChain->id) ?: $newChain->withoutEvents();

        if (!$this->assertSignedByUs($newChain, $chain)) {
            return; // Forbidden
        }

        $handled = $this->manager->add($chain, $newChain);

        if ($handled->failed()) {
            $this->badRequest((string)json_encode($handled->getErrors()));
            return;
        }

        $this->output($chain, 'json');
    }


    /**
     * Create a new chain from input data.
     *
     * @return EventChain|null
     */
    protected function createChainFromInput(): ?EventChain
    {
        $data = $this->getInput();

        $newChain = EventChain::create()->setValues($data);
        $validation = $newChain->validate();

        if ($validation->failed()) {
            $this->badRequest((string)json_encode($validation->getErrors()));
            return null;
        }

        return $newChain;
    }

    /**
     * Check if event is from identity related to this node.
     *
     * @param EventChain  $newChain
     * @param EventChain  $chain
     * @param string|null $node
     * @return bool
     */
    protected function assertSignedByUs(EventChain $newChain, EventChain $chain, ?string $node = null): bool
    {
        if (!$this->dispatcher->isEnabled()) {
            return true;
        }

        $lastEvent = $newChain->getLastEvent();
        $signedByUs = $chain->getNodes() === [] || $chain->isEventSignedByIdentityNode($lastEvent, $node);

        if (!$signedByUs) {
            $this->forbidden('Not allowed to send to this node from given origin');
        }

        return $signedByUs;
    }
}
