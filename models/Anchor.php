<?php

/**
 * Class to interact with anchor service
 */
class Anchor
{
    /**
     * @var object
     */
    protected $config;

    /**
     * @var GuzzleHttp\ClientInterface
     */
    protected $httpClient;
    
    
    /**
     * Class constructor
     * 
     * @param object|array      $config
     * @param GuzzleHttp\Client $httpClient
     */
    public function __construct($config, GuzzleHttp\ClientInterface $httpClient)
    {
        $this->config = (object)$config;
        $this->httpClient = $httpClient;
    }
    
    
    /**
     * Anchor the given hash
     * 
     * @param string  $hash
     * @param string  $encoding
     */
    public function hash($hash, $encoding = 'base58')
    {
        $endpoint = $this->config->url;
        $url = "{$endpoint}/hash";
        $payload = compact('hash', 'encoding');
        
        $options = [
            'json' => $payload,
            'http_errors' => true,
            'query' => []
        ];
        
        $this->httpClient->post($url, $options);
    }
}
