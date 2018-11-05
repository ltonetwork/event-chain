<?php declare(strict_types=1);

namespace AddEventStep;

use Event;
use EventChain;
use EventChainGateway;
use Improved\IteratorPipeline\Pipeline;

/**
 * Everything has checked out and the event has been processed. It is added to the event chain and stored to the DB.
 */
class SaveEvent
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var EventChainGateway
     */
    protected $chainGateway;

    /**
     * SaveEvent constructor.
     *
     * @param EventChain        $chain
     * @param EventChainGateway $chainGateway
     */
    public function __construct(EventChain $chain, EventChainGateway $chainGateway)
    {
        $this->chain = $chain;
        $this->chainGateway = $chainGateway;
    }

    /**
     * Invoke the step
     *
     * @param Pipeline $pipeline
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline): Pipeline
    {
        return $pipeline->apply(function(Event $event) {
            $this->chain->events->add($event);
            $this->chainGateway->save($this->chain);
        });
    }
}
