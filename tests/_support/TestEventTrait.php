<?php declare(strict_types=1);

use LTO\Account;
use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Improved\Iterator\CombineIterator;

trait TestEventTrait
{
    /**
     * @return Account
     */
    protected function getNode(): Account
    {
        return App::getContainer()->get('node.account');
    }

    /**
     * Get existing chain
     *
     * @codeCoverageIgnore
     * @param string $id 
     * @return EventChain|null
     */
    public function getExistingChain($id)
    {
        $gateway = App::getContainer()->get(EventChainGateway::class);
        
        return $gateway->fetch($id);
    }

    /**
     * Get chain data to send in request
     * @param  EventChain $chain
     * @return array
     */
    public function castChainToData(EventChain $chain): array
    {
        return json_decode(json_encode($chain), true);
    }

    /**
     * Decode event body
     * @param  string $body  Encoded event body
     * @return array
     */
    public function decodeEventBody($body): array
    {
        $data = base58_decode($body);        

        return json_decode($data, true);
    }

    /**
     * Create test event chain
     *
     * @param int $eventsCount
     * @param array|null $bodies 
     * @return EventChain
     */
    public function createEventChain(int $eventsCount, ?array $bodies = null): EventChain
    {
        $node = $this->getNode();
        $chain = $this->createChain($node);

        for ($i=0; $i < $eventsCount; $i++) { 
            $body = $this->createEventBody($i, $bodies);
            $event = $this->createEvent($chain, $node, $body);

            $chain->events->add($event);
        }

        return $chain;
    }

    /**
     * Create fork for given chain
     *
     * @param EventChain $chain
     * @param int        $startIdx
     * @param int        $countNewEvents
     * @param array|null $bodies
     * @return EventChain
     */
    public function createFork(EventChain $chain, int $startIdx, int $countNewEvents, ?array $bodies = null): EventChain
    {
        $node = App::getContainer()->get('node.account');
        $fork = $chain->withEvents([]);

        for ($i=0; $i < $startIdx; $i++) { 
            $fork->events->add($chain->events[$i]);
        }

        for ($i=0; $i < $countNewEvents; $i++) { 
            $body = $this->createEventBody($i, $bodies, ['foo' => 'zoo']);
            $event = $this->createEvent($fork, $node, $body);
            $fork->events->add($event);
        }

        return $fork;
    }

    /**
     * Clone the chain and add events.
     *
     * @param EventChain $chain
     * @param int $eventsCount
     * @param array|null $bodies 
     * @param bool $asPartial    Return chain with only added events, skipping previous
     * @return EventChain
     */
    public function addEvents(EventChain $chain, int $eventsCount, ?array $bodies = null, bool $asPartial = false): EventChain
    {
        $node = $this->getNode();
        $newChain = clone $chain;
        $countExist = $chain->events->count();

        for ($i = 0; $i < $eventsCount; $i++) {
            $body = $this->createEventBody($i, $bodies);
            $newChain->events[] = $this->createEvent($newChain, $node, $body);
        }

        if ($asPartial) {
            $array = $newChain->events->getArrayCopy();
            $events = array_slice($array, $countExist);
            $newChain = $newChain->withEvents($events);
        }

        return $newChain;
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
        $startIdx = $totalCount - $eventsCount;

        for ($i = $startIdx; $i < $totalCount; $i++) { 
            $events[] = $originalChain->events[$i];
        }

        return $originalChain->withEvents($events);
    }

    /**
     * Create pipeline with events pairs
     *
     * @param EventChain $keysChain
     * @param EventChain $valuesChain 
     * @return Pipeline
     */
    public function mapChains(EventChain $keysChain, EventChain $valuesChain): Pipeline
    {
        $values = $valuesChain->events;
        $keys = iterator_to_array($keysChain->events);
        $keys = array_pad($keys, count($values), null);

        return Pipeline::with(new CombineIterator($keys, $values));
    }

    /**
     * Create valid event chain
     *
     * @param LTO\Account $node
     * @return EventChain
     */
    protected function createChain(Account $node)
    {
        $ltoChain = new \LTO\EventChain();
        $ltoChain->initFor($node);

        $chain = (new TypeCast($ltoChain))->to(EventChain::class);

        return $chain;
    }

    /**
     * Create valid Event
     *
     * @param EventChain  $chain
     * @param LTO\Account $node
     * @param array       $body
     * @return Event
     */
    protected function createEvent(EventChain $chain, ?Account $node = null, array $body = []): Event
    {
        $node = $node ?? $this->getNode();

        $event = new Event();

        $values = [
            'origin' => 'localhost',
            'timestamp' => (new DateTime)->getTimestamp(),
            'previous' => $chain->getLatestHash(),
            'body' => base58_encode(json_encode($body))
        ];

        $event->setValues($values);
        $event->signWith($node);

        return $event;
    }

    /**
     * Create body for event
     *
     * @param int $idx 
     * @param array $bodies
     * @param array|null $default 
     * @return array
     */
    protected function createEventBody(int $idx, ?array $bodies, ?array $default = null): array
    {
        $body = $default ?: ['foo' => 'bar'];

        if (isset($bodies)) {
            if (!isset($bodies[$idx])) {
                throw new InvalidArgumentException("No body for test event [$idx]");
            }

            $body = $bodies[$idx];
        }

        return $body;
    }
}
