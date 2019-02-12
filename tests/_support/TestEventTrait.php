<?php

trait TestEventTrait
{
    /**
     * Create test event chain
     *
     * @param int $eventsCount
     * @return EventChain
     */
    public function createEventChain(int $eventsCount, Account $node): EventChain
    {
        $ltoChain = (new \LTO\EventChain())->initFor($node);
        $chain = (new TypeCast($ltoChain))->to(EventChain::class);

        for ($i=0; $i < $eventsCount; $i++) { 
            $event = new Event();

            $values = [
                'timestamp' => (new DateTime)->getTimestamp(),
                'previous' => $chain->getLatestHash()
            ];

            $event->setValues($values);
            $event->signWith($node);

            $chain->events->add($event);
        }

        return $chain;
    }

    /**
     * Create test partial chain
     *
     * @param EventChain $originalChain
     * @param int $eventsCount 
     * @return EventChain
     */
    public function createPartialChain(EventChain $originalChain, int $eventsCount): EventChain
    {
        $events = [];
        $totalCount = count($originalChain->events);
        $startIdx = $totalCount - $eventsCount + 1;

        for ($i = $startIdx; $i < $totalCount; $i++) { 
            $events[] = $originalChain->events[$i];
        }

        return $originalChain->withEvents($events);
    }
}
