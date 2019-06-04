<?php declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\ClientInterface as HttpClient;
use Psr\Http\Message\ResponseInterface as Response;
use LTO\Account;

/**
 * Class to store an external resource.
 */
class ResourceStorage
{
    use ResourceService\ExtractFromResponseTrait;
    use ResourceService\InjectEventChainTrait;

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
     * Store a resource.
     * Return any new events created by storing the resource.
     *
     * @param ResourceInterface $resource
     * @param EventChain $chain
     * @return EventChain
     */
    public function store(ResourceInterface $resource, EventChain $chain): EventChain
    {
        $partial = $chain->getPartialWithoutEvents();

        Pipeline::with($this->endpoints)
            ->filter(static function($endpoint) use ($resource) {
                return $endpoint->schema === null || $resource->getSchema() === $endpoint->schema;
            })
            ->filter(static function($endpoint) {
                return !isset($endpoint->grouped);
            })
            ->map(function($endpoint) use ($resource, $partial) {
                $resource = $this->injectEventChain($resource, $endpoint, $partial);

                return $this->sendStoreRequest($resource, $endpoint, $partial);
            })
            ->map(function(Response $response) {
                return $this->getEventsFromResponse($response);
            })
            ->filter(function(?EventChain $newEvents) use ($chain) {
                if ($newEvents !== null && $newEvents->id !== $chain->id) {
                    trigger_error("Ignoring additional events; chain mismatch", E_USER_WARNING);
                    return false;
                }

                return $newEvents !== null && $newEvents->events !== [];
            })
            ->apply(function($newEvents) use ($partial) {
                foreach ($newEvents->events as $event) {
                    $partial->events[] = $event;
                }
            })
            ->walk();

        return $partial;
    }

    /**
     * Send request
     *
     * @param ResourceInterface $resource
     * @param stdClass          $endpoint
     * @param EventChain        $chain
     * @return Response
     */
    protected function sendStoreRequest(ResourceInterface $resource, stdClass $endpoint, EventChain $chain): Response
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

        return $this->httpClient->request('POST', $endpoint->url, $options);
    }
}
