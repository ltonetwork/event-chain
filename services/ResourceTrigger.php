<?php declare(strict_types=1);

use LTO\Account;
use GuzzleHttp\ClientInterface as HttpClient;
use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\Promise;

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
            foreach ($endpoint->resources as $groupOpts) {
                $groupPromises = Pipeline::with($resources)
                    ->filter(static function(ResourceInterface $resource) use ($groupOpts) {
                        return $groupOpts->schema === null || $resource->getSchema() === $groupOpts->schema;
                    })
                    ->group(static function(ResourceInterface $resource) use ($groupOpts) {
                        $field = $groupOpts->group->process;
                        $value = $resource->{$field} ?? null;

                        return is_scalar($value) ? $value : $value->id ?? null;
                    })
                    ->cleanup()
                    ->keys()
                    ->map(function($value) use ($endpoint, $groupOpts, $chain) {
                        $field = $groupOpts->group->process;
                        $data = (object)[$field => $value];
                        $data = $this->injectEventChain($data, $endpoint, $chain);

                        $options = [
                            'json' => $data, 
                            'http_errors' => true,
                            'signature_key_id' => base58_encode($this->node->sign->publickey)
                        ];

                        return $this->httpClient->requestAsync('POST', $endpoint->url, $options);
                    })
                    ->toArray();                

                $promises = array_merge($promises, $groupPromises);
            }
        }

        Promise\unwrap($promises);
    }

    /**
     * Inject event chain into query data
     *
     * @param object $resource
     * @param object $endpoint
     * @param EventChain|null $chain
     * @return stdClass
     */
    protected function injectEventChain(object $data, object $endpoint, ?EventChain $chain): stdClass
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