<?php

declare(strict_types=1);

namespace ResourceService;

use EventChain;
use ResourceInterface;

/**
 * Trait to inject the event chain into a resource.
 */
trait InjectEventChainTrait
{
    /**
     * Inject event chain into query data
     *
     * @param object     $data
     * @param object     $endpoint
     * @param EventChain $chain
     * @return object
     */
    protected function injectEventChain(object $data, object $endpoint, EventChain $chain): object
    {
        if (!isset($endpoint->inject_chain) || !$endpoint->inject_chain) {
            return $data;
        }

        $data = clone $data;

        if ($endpoint->inject_chain === 'empty') {
            $chain = $chain->getPartialWithoutEvents();
        }

        $data->chain = $chain;

        return $data;
    }
}
