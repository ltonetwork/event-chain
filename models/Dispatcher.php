<?php

/**
 * Class to interact with event dispatcher service
 */
class Dispatcher
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
     * Add the event to the queue of the node
     * 
     * @param EventChain $chain
     * @param string[]   $to     If specified will send the event to the nodes in this array
     */
    public function queue(EventChain $chain, $to = null)
    {
        $endpoint = $this->config->url;
        $url = "{$endpoint}/queue";
        
        $options = [
            'json' => $chain,
            'http_errors' => true,
            'query' => []
        ];
        
        if (isset($to) && !empty($to)) {
            $options['query']['to'] = $to;
        }

        $this->httpClient->post($url, $options);
    }
}
