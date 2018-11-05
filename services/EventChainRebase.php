<?php declare(strict_types=1);

/**
 * Service to rebase a fork of an event chain upon the leading chain.
 */
class EventChainRebase
{
    /**
     * @var EventChainGateway
     */
    protected $gateway;

    /**
     * EventChainRebase constructor.
     *
     * @param EventChainGateway $gateway
     */
    public function __construct(EventChainGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Rebase fork onto chain.
     *
     * @param EventChain $chain
     * @param EventChain $fork
     * @return EventChain
     */
    public function rebase(EventChain $chain, EventChain $fork): EventChain
    {
        // TODO implement rebase function.

        return $chain;
    }

    /**
     * Alias of `rebase()`
     *
     * @param EventChain $chain
     * @param EventChain $fork
     * @return EventChain
     */
    final public function __invoke(EventChain $chain, EventChain $fork): EventChain
    {
        return $this->rebase($chain, $fork);
    }
}
