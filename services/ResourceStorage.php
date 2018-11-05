<?php declare(strict_types=1);

use Improved as i;
use const Improved\FUNCTION_ARGUMENT_PLACEHOLDER as __;
use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class to store an external resource.
 *
 * @todo sign requests
 */
class ResourceStorage
{
    /**
     * @var ResourceMapping
     */
    protected $mapping;
    
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var HttpErrorWarning
     */
    protected $errorWarning;

    
    /**
     * Class constructor
     *
     * @param ResourceMapping  $mapping     URI to URL mapping
     * @param HttpClient       $httpClient
     * @param HttpErrorWarning $errorWarning
     */
    public function __construct(ResourceMapping $mapping, HttpClient $httpClient, HttpErrorWarning $errorWarning)
    {
        $this->mapping = $mapping;
        $this->httpClient = $httpClient;
        $this->errorWarning = $errorWarning;
    }


    /**
     * Store a resource
     *
     * @param ResourceInterface $resource
     */
    public function store(ResourceInterface $resource): void
    {
        if (!$resource instanceof ExternalResource) {
            return;
        }
        
        $url = $this->mapping->getUrl($resource->getId());
        $this->httpClient->request('POST', $url, ['json' => $resource, 'http_errors' => true]);
    }

    /**
     * Message resources that the event chain has been processed.
     *
     * @param iterable<ResourceInterface> $resources
     * @param EventChain         $chain
     */
    public function done(iterable $resources, EventChain $chain): void
    {
        $data = [
            'id' => $chain->getId(),
            'lastHash' => $chain->getLatestHash()
        ];

        $promises = Pipeline::with($resources)
            ->filter(function(ResourceInterface $resource) {
                return $resource instanceof ExternalResource && $this->mapping->hasDoneUrl($resource->getId());
            })
            ->map(function(ExternalResource $resource) {
                return $this->mapping->getDoneUrl($resource->getId());
            })
            ->map(function(string $url) use ($data) {
                return $this->httpClient->requestAsync('POST', $url, ['json' => $data, 'http_errors' => false])
                    ->then(i\function_partial($this->errorWarning, __, $url));
            })
            ->toArray();

        GuzzleHttp\Promise\unwrap($promises);
    }

    /**
     * Delete all projected resources
     *
     * @param iterable $resources
     * @return void
     */
    public function deleteProjected(iterable $resources): void
    {
        $promises = Pipeline::with($resources)
            ->filter(function(ResourceInterface $resource) {
                return $resource instanceof ExternalResource && $this->mapping->hasDoneUrl($resource->getId());
            })
            ->map(function(ExternalResource $resource) {
                return $this->mapping->getDoneUrl($resource->getId());
            })
            ->map(function(string $url) {
                return $this->httpClient->requestAsync('DELETE', $url, ['http_errors' => false])
                    ->then(i\function_partial($this->errorWarning, __, $url));
            })
            ->toArray();

        GuzzleHttp\Promise\unwrap($promises);
    }
}
