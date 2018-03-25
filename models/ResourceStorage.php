<?php

/**
 * Class to store an external resource
 */
class ResourceStorage
{
    /**
     * @var array
     */
    protected $mapping;
    
    /**
     * @var GuzzleHttp\Client 
     */
    protected $httpClient;
    
    
    /**
     * Class constructor
     * 
     * @param array             $mapping  URI to URL mapping
     * @param GuzzleHttp\Client $httpClient
     */
    public function __construct($mapping, GuzzleHttp\Client $httpClient)
    {
        $this->mapping = $mapping;
        $this->httpClient = $httpClient;
    }
    
    /**
     * Get an URL from a URI
     * 
     * @param type $uri
     * @return string
     */
    public function getURL($uri)
    {
        foreach ($this->mapping as $search => $url) {
            if (jasny\str_starts_with($uri, $search)) {
                return $url;
            }
        }
        
        throw new OutOfRangeException("Not URL found for '$uri'");
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
        
        $url = $this->getURL($resource->id);
        
        $this->httpClient->post($url, ['json' => $resource, 'http_errors' => true]);
    }
}
