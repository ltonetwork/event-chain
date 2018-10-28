<?php

/**
 * Controller for processing new events.
 */
class EventController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * Data gateway to fetch/save event chains from/to the database.
     * @var Gateway
     */
    protected $eventChains;

    /**
     * Interact with (external) event dispatcher service.
     * @var Dispatcher
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
     * @param Gateway      $eventChainGateway  "models.event_chains"
     * @param Dispatcher   $dispatcher
     * @param EventManager $eventManager
     */
    public function __construct(Gateway $eventChainGateway, Dispatcher $dispatcher, EventManager $eventManager)
    {
        $this->eventChains = $eventChainGateway;
        $this->dispatcher = $dispatcher;
        $this->manager = $eventManager;
    }

    /**
     * Before each action
     */
    public function before()
    {
        $this->byDefaultSerializeTo('json');
    }


    /**
     * Add the chain to the queue.
     */
    public function queueAction()
    {
        $newChain = $this->createChainFromInput();

        if (!isset($newChain)) {
            return; // Bad request
        }

        $chain = $this->eventChains->fetch($newChain->id) ?: $newChain->withoutEvents();

        if (!$this->assertSignedByUs($newChain, $chain)) {
            return; // Forbidden
        }

        // @todo: Add checks from $manager->add() here as well. Now the request is accepted and fails when processing.

        $this->dispatcher->queueToSelf($newChain);

        return $this->noContent();
    }

    /**
     * Add a new chain or new events to an existing chain.
     */
    public function processAction()
    {
        $newChain = $this->createChainFromInput();

        if (!isset($newChain)) {
            return; // Bad request
        }

        $chain = EventChain::fetch($newChain->id) ?: $newChain->withoutEvents();

        if (!$this->assertSignedByUs($newChain, $chain)) {
            return; // Forbidden
        }

        $handled = $this->manager->with($chain)->add($newChain);

        if ($handled->failed()) {
            return $this->badRequest($handled->getErrors());
        }

        $this->output($chain, 'json');
        return $this->ok();
    }


    /**
     * Create a new chain from input data.
     *
     * @return EventChain
     */
    protected function createChainFromInput(): EventChain
    {
        $data = $this->getInput();

        $newChain = EventChain::create()->setValues($data);
        $validation = $newChain->validate();

        if ($validation->failed()) {
            return $this->badRequest($validation->getErrors());
        }

        return $newChain;
    }

    /**
     * Check if event is from identity related to this node.
     *
     * @return bool
     */
    protected function assertSignedByUs(EventChain $newChain, EventChain $chain): bool
    {
        $node = $this->dispatcher->getNode();
        $lastEvent = $newChain->getLastEvent();

        $signedByUs = empty($chain->getNodes()) || $chain->isEventSignedByIdentityNode($lastEvent, $node);

        if (!$signedByUs) {
            $this->forbidden('Not allowed to send to this node from given origin');
        }

        return $signedByUs;
    }
}
