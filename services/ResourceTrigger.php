<?php declare(strict_types=1);

use LTO\Account;
use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Psr7\Response as HttpResponse;

/**
 * Class to trigger on an external resource change.
 * In contrary to storing resources, triggers are executed when all events are processed.
 */
class ResourceTrigger
{
    use ResourceService\ExtractFromResponseTrait;
    use ResourceService\InjectEventChainTrait;

    /**
     * @var array
     */
    protected $triggers;

    /**
     * @var HttpClient
     */
    protected $httpClient;

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
     * @param Account          $node
     */
    public function __construct(array $triggers, HttpClient $httpClient, Account $node)
    {
        $this->endpoints = $triggers;
        $this->httpClient = $httpClient;
        $this->node = $node;
    }

    /**
     * Message resources that the event chain has been processed.
     *
     * @param iterable $resources
     * @param EventChain $chain
     * @return EventChain|null    Events created after triggering some workflow actions
     */
    public function trigger(iterable $resources, EventChain $chain): ?EventChain
    {
        if ($resources instanceof Traversable) {
            $resources = iterator_to_array($resources);
        }

        $partial = $chain->withoutEvents();

        Pipeline::with($this->endpoints)
            ->unwind('resources')
            ->map(function($endpoint) use($resources, $chain) {
                return $this->triggerEndpoint($resources, $chain, $endpoint);
            })
            ->flatten()
            ->apply(function(EventChain $newEvents) use ($partial) {
                foreach ($newEvents->events as $event) {
                    $partial->events[] = $event;
                }
            })
            ->walk();

        return $partial;
    }

    /**
     * Run trigger of a single endpoint.
     * Additional events returned by the trigger are added to the event chain.
     *
     * @param array      $resources
     * @param EventChain $chain
     * @param string     $endpoint
     * @param array      $opts
     * @return iterable
     */
    protected function triggerEndpoint(array $resources, EventChain $chain, stdClass $endpoint): iterable
    {
        $opts = $endpoint->resources;

        return Pipeline::with($resources)
            ->filter(static function(ResourceInterface $resource) use ($opts) {
                return $opts->schema === null || $resource->getSchema() === $opts->schema;
            })
            ->group(static function(ResourceInterface $resource) use ($opts) {
                $data = null;

                foreach ($opts->group as $key => $field) {
                    $data[$key] = $resource->{$field} ?? null;
                }

                return $data;
            })
            ->cleanup()
            ->keys()
            ->map(function(array $data) use ($endpoint, $chain) {
                return $this->sendRequest((object)$data, $endpoint, $chain);
            })
            ->map(function(HttpResponse $response) {
                return $this->getEventsFromResponse($response);
            })
            ->filter(function(?EventChain $newEvents) use ($chain) {
                if ($newEvents !== null && $newEvents->id !== $chain->id) {
                    trigger_error("Ignoring additional events; chain mismatch", E_USER_WARNING);
                    return false;
                }

                return $newEvents !== null && $newEvents->events !== [];
            });
    }

    /**
     * Send request
     *
     * @param stdClass   $data
     * @param stdClass   $endpoint
     * @param EventChain $chain
     * @return GuzzleHttp\Psr7\Response
     */
    protected function sendRequest(stdClass $data, stdClass $endpoint, EventChain $chain)
    {
        $data = $this->injectEventChain($data, $endpoint, $chain);

        $options = [
            'headers' => [
                'X-Event-Chain' => $chain->id . ':' . $chain->getLatestHash(),
            ],
            'json' => $data,
            'http_errors' => true,
            'signature_key_id' => base58_encode($this->node->sign->publickey)
        ];

        return $this->httpClient->request('POST', $endpoint->url, $options);
    }
}