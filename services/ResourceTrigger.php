<?php declare(strict_types=1);

/**
 * Class to trigger on an external resource change.
 * In contrary to storing resources, triggers are executed when all events are processed.
 */
class ResourceTrigger
{
    /**
     * @var array
     */
    protected $triggers;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var HttpErrorWarning
     */
    protected $errorWarning;

    /**
     * @var Account
     **/
    protected $node;

    /**
     * @var Digest
     **/
    protected $digest;

    /**
     * Class constructor
     *
     * @param string[]         $triggers
     * @param HttpClient       $httpClient
     * @param HttpErrorWarning $errorWarning
     * @param Account          $node
     */
    public function __construct(
        array $triggers,
        HttpClient $httpClient,
        HttpErrorWarning $errorWarning,
        Account $node
    )
    {
        $this->endpoints = $triggers;
        $this->httpClient = $httpClient;
        $this->errorWarning = $errorWarning;
        $this->node = $node;
    }

    /**
     * Message resources that the event chain has been processed.
     *
     * @param iterable $resources
     * @param EventChain|null $chain
     */
    public function trigger(iterable $resources, ?EventChain $chain = null): void
    {
        $promises = [];

        foreach ($this->endpoints as $endpoint) {
            if (!isset($endpoint->grouped)) {
                continue;
            }

            $endpointPromises = Pipeline::with($resources)
                ->filter(static function(ResourceInterface $resource) use ($endpoint) {
                    return $endpoint->schema === null || $resource->getSchema() === $endpoint->schema;
                })
                ->group(static function(ResourceInterface $resource) use ($endpoint) {
                    $field = $endpoint->grouped;
                    $value = $resource->{$field} ?? null;

                    return is_scalar($value) ? $value : $value->id ?? null;
                })
                ->cleanup()
                ->keys()
                ->map(function($value) use ($endpoint, $chain) {
                    $field = $endpoint->grouped;
                    $data = (object)[$field => $value];
                    $data = $this->injectEventChain($data, $endpoint, $chain);

                    $options = ['json' => $data, 'http_errors' => true];

                    return $this->httpClient->requestAsync('POST', $endpoint->url, $options);
                })
                ->toArray();

            $promises = array_merge($promises, $endpointPromises);
        }

        Promise\unwrap($promises);
    }

    /**
     * Inject event chain into query data
     *
     * @param object $resource
     * @param object $endpoint
     * @param EventChain|null $chain
     * @return ResourceInterface
     */
    protected function injectEventChain(object $data, object $endpoint, ?EventChain $chain): ResourceInterface
    {
        if (!isset($chain) || !isset($endpoint->inject_chain) || !$endpoint->inject_chain) {
            return $data;
        }

        $data = clone $data;

        if ($endpoint->inject_chain === 'empty') {
            $latestHash = $chain->getLatestHash();
            $chain = $chain->withoutEvents();
            $chain->latest_hash = $latestHash;
        }

        $data->chain = $chain;

        return $data;
    }
}