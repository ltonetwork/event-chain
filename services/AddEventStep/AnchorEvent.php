<?php declare(strict_types=1);

namespace AddEventStep;

use AnchorClient;
use Event;
use EventChain;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use LTO\Account;

/**
 * Submit the hash of the event to the anchoring service which stores the hash on the public LTO network blockchain.
 */
class AnchorEvent
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * @var Account
     */
    protected $node;

    /**
     * @var AnchorClient
     */
    protected $anchor;


    /**
     * AnchorEvent constructor.
     *
     * @param EventChain $chain
     * @param Account $node
     * @param AnchorClient $anchor
     */
    public function __construct(EventChain $chain, Account $node, AnchorClient $anchor)
    {
        $this->chain = $chain;
        $this->node = $node;
        $this->anchor = $anchor;
    }

    /**
     * Invoke the step.
     *
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation): Pipeline
    {
        return $pipeline->apply(function(Event $event) use ($validation) {
            if ($validation->failed() || !$this->chain->isEventSignedByAccount($event, $this->node)) {
                return;
            }

            $this->anchor->submit($event->hash);
        });
    }
}
