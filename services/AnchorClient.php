<?php

use GuzzleHttp\ClientInterface as HttpClient;

/**
 * Class to interact with anchor service
 */
class AnchorClient
{
    /**
     * @var object
     */
    protected $config;

    /**
     * @var HttpClient
     */
    protected $httpClient;
    
    
    /**
     * Class constructor
     * 
     * @param object|array $config
     * @param HttpClient   $httpClient
     */
    public function __construct($config, GuzzleHttp\ClientInterface $httpClient)
    {
        $this->config = (object)$config;
        $this->httpClient = $httpClient;
    }
    
    
    /**
     * Anchor the given hash.
     * 
     * @param string $hash
     * @param string $encoding
     */
    public function submit($hash, $encoding = 'base58'): void
    {
        $url = "{$this->config->url}/hash";

        $options = [
            'json' => compact('hash', 'encoding'),
            'http_errors' => true,
            'query' => []
        ];
        
        $this->httpClient->post($url, $options);
    }
}
