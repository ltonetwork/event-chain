<?php declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface as HttpClient;

/**
 * Class to store an external resource.
 * 
 * @todo sign requests
 */
class ResourceStorage
{
    /**
     * @var array
     */
    protected $mapping;
    
    /**
     * @var HttpClient
     */
    protected $httpClient;
    
    /**
     * URL for requests that need to be called on done
     * @var string[]
     */
    protected $pending = [];
    
    
    /**
     * Class constructor
     * 
     * @param array      $mapping     URI to URL mapping
     * @param HttpClient $httpClient
     */
    public function __construct(array $mapping, HttpClient $httpClient)
    {
        $this->mapping = $mapping;
        $this->httpClient = $httpClient;
    }
    
    /**
     * Try to get an URL from a URI
     * 
     * @param string $uri
     * @return string|null
     */
    protected function findURL(string $uri): ?string
    {
        $url = null;
        $uriBase = preg_replace('/\?.*/', '', $uri);
        
        foreach ($this->mapping as $search => $endpoint) {
            if (!Jasny\fnmatch_extended($search, $uriBase)) {
                continue;
            }
            
            $parts = explode('/', $uriBase);
            $url = preg_replace_callback('/\\$(\d+)/', function ($match) use ($parts) {
                $i = $match[1];
                return $parts[$i];
            }, $endpoint);

            break;
        }
        
        return $url;
    }
    
    /**
     * Check if URI has a URL
     * 
     * @param string $uri
     * @return bool
     */
    public function hasUrl($uri): bool
    {
        return $this->findURL($uri) !== null;
    }
    
    /**
     * Get an URL from a URI
     * 
     * @param string $uri
     * @return string
     * @throws OutOfRangeException if not URL exist for the URI
     */
    public function getUrl($uri): string
    {
        $url = $this->findURL($uri);
        
        if (!isset($url)) {
            throw new OutOfRangeException("Not URL found for '$uri'");
        }
        
        return $url;
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
        
        $url = $this->getUrl($resource->getId());
        $this->httpClient->request('POST', $url, ['json' => $resource, 'http_errors' => true]);
        
        $doneUri = preg_replace('/\?.*$/', '', $resource->getId()) . '/done';
        if ($this->hasUrl($doneUri)) {
            $this->pending[] = $doneUri;
        }
    }
    
    /**
     * Message resources that the event chain has been processed.
     *
     * @param EventChain $chain
     * @throws \Throwable
     */
    public function done(EventChain $chain): void
    {
        $data = [
            'id' => $chain->getId(),
            'lastHash' => $chain->getLatestHash()
        ];
        
        $promises = [];

        foreach ($this->pending as $uri) {
            $url = $this->getUrl($uri);

            $promises[] = $this->httpClient->requestAsync('POST', $url, ['json' => $data, 'http_errors' => false])
                ->then(function(Response $response) use ($url) {
                    if ($response->getStatusCode() >= 400) {
                        $this->doneOnError($response, $url);
                    }
                });
        }

        GuzzleHttp\Promise\unwrap($promises);
    }

    /**
     * Handler an error on done
     *
     * @param Response $response
     * @param string $url
     */
    protected function doneOnError(Response $response, string $url): void
    {
        $status = $response->getStatusCode() . ' ' . $response->getReasonPhrase();

        $hasMessage = $response->getStatusCode() < 500
            && preg_match('~^(text/plain|application/json)(;|$)~', $response->getHeaderLine('Content-Type'));
        $message = $hasMessage ? ': ' . $response->getBody() : '';

        trigger_error("POST $url resulted in a `$status` response" . $message, E_USER_WARNING);
    }
}
