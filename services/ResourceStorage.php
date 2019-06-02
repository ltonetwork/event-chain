<?php declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Promise;
use LTO\Account;

/**
 * Class to store an external resource.
 */
class ResourceStorage
{
    /**
     * @var array
     */
    protected $endpoints;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var Account
     **/
    protected $node;

    /**
     * Class constructor
     *
     * @param array            $endpoints
     * @param HttpClient       $httpClient
     * @param Account          $node
     */
    public function __construct(array $endpoints, HttpClient $httpClient, Account $node)
    {
        $this->endpoints = $endpoints;
        $this->httpClient = $httpClient;
        $this->node = $node;
    }

    /**
     * Store a resource
     *
     * @param ResourceInterface $resource
     * @param EventChain $chain
     */
    public function store(ResourceInterface $resource, EventChain $chain): void
    {
        $promises = Pipeline::with($this->endpoints)
            ->filter(static function($endpoint) use ($resource) {
                return $endpoint->schema === null || $resource->getSchema() === $endpoint->schema;
            })
            ->filter(static function($endpoint) {
                return !isset($endpoint->grouped);
            })
            ->map(function($endpoint) use ($resource, $chain) {
                $resource = $this->injectEventChain($resource, $endpoint, $chain);

                return $this->sendStoreRequest($resource, $endpoint);
            })
            ->toArray();

        Promise\unwrap($promises);
    }

    /**
     * Send request
     *
     * @param ResourceInterface $resource
     * @param stdClass          $endpoint
     * @return GuzzleHttp\Promise\PromiseInterface
     */
    protected function sendStoreRequest(ResourceInterface $resource, stdClass $endpoint, EventChain $chain)
    {
        $options = [
            'json' => $resource,
            'http_errors' => true,
            'signature_key_id' => base58_encode($this->node->sign->publickey),
            'headers' => [
                'X-Original-Key-Id' => $resource->original_key,
                'X-Event-Chain' => $chain->id . ':' . $chain->getLatestHash(),
                'Content-Type' => 'application/json',
                'date' => date(DATE_RFC1123)
            ]
        ];

        $url = $this->expandUrl($endpoint->url, $resource);

        return $this->httpClient->requestAsync('POST', $url, $options);
    }

    /**
     * Delete all resources.
     * @todo Fix this method. Deleting resources is currently disabled.
     *
     * @param iterable<ResourceInterface> $resources
     */
    public function deleteResources(iterable $resources): void
    {
        //temp
        throw new Exception('deleteResources method is disabled');

        $promises = Pipeline::with($resources)
            ->filter(function (ResourceInterface $resource) {
                return $resource instanceof ExternalResource && $this->mapping->hasDoneUrl($resource->getId());
            })
            ->map(function (ExternalResource $resource) {
                return $this->mapping->getDoneUrl($resource->getId());
            })
            ->map(function (string $url) {
                return $this->httpClient->requestAsync('DELETE', $url, ['http_errors' => false]);
            })
            ->toArray();

        Promise\unwrap($promises);
    }

    /**
     * Inject event chain into query data
     *
     * @param object $resource
     * @param object $endpoint
     * @param EventChain $chain
     * @return ResourceInterface
     */
    protected function injectEventChain(object $data, object $endpoint, EventChain $chain): ResourceInterface
    {
        if (!isset($endpoint->inject_chain) || !$endpoint->inject_chain) {
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

    /**
     * Insert parameter value into endpoint url
     *
     * @param string $url
     * @param ResourceInterface $resource
     * @return string
     */
    protected function expandUrl(string $url, ResourceInterface $resource): string
    {
        $regexp = '~/-(/|$)~';

        if (!preg_match($regexp, $url)) {
            return $url;
        }

        return preg_replace($regexp, "/{$resource->process}$1", $url);
    }
}
