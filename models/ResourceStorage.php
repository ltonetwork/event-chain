<?php

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
     * @var GuzzleHttp\ClientInterface
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
     * @param array             $mapping     URI to URL mapping
     * @param GuzzleHttp\Client $httpClient
     */
    public function __construct(array $mapping, GuzzleHttp\ClientInterface $httpClient)
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
    protected function findURL($uri)
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
     * @return boolean
     */
    public function hasUrl($uri)
    {
        return $this->findURL($uri) !== null;
    }
    
    /**
     * Get an URL from a URI
     * 
     * @param string $uri
     * @return string
     * @throws OutOfBoundsException
     */
    public function getUrl($uri)
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
     * @param Resource $resource
     */
    public function store(Resource $resource)
    {
        if (!$resource instanceof ExternalResource) {
            return;
        }
        
        $url = $this->getURL($resource->getId());
        $this->httpClient->post($url, ['json' => $resource, 'http_errors' => true]);
        
        $doneUri = preg_replace('/\?.*$/', '', $resource->getId()) . '/done';
        if ($this->hasUrl($doneUri)) {
            $this->pending[] = $doneUri;
        }
    }
    
    /**
     * Message resources that the event chain has been processed.
     * 
     * @param EventChain $chain
     */
    public function done(EventChain $chain)
    {
        $data = [
            'id' => $chain->getId(),
            'lastHash' => $chain->getLatestHash()
        ];
        
        $promises = [];
        
        foreach ($this->pending as $uri) {
            $url = $this->getUrl($uri);
            $promises[] = $this->httpClient->postAsync($url, ['json' => $data, 'http_errors' => true]);
        }
        
        GuzzleHttp\Promise\unwrap($promises);
    }
}
