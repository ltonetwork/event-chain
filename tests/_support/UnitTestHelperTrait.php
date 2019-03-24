<?php declare(strict_types=1);

use LTO\Account;
use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Improved\Iterator\CombineIterator;

trait UnitTestHelperTrait
{
    /**
     * Get mock for http client
     *
     * @param array $container
     * @param array $responses 
     * @return GuzzleHttp\Client
     */
    protected function getHttpClientMock(array &$container, array $responses)
    {
        $mock = new GuzzleHttp\Handler\MockHandler($responses);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        
        $history = GuzzleHttp\Middleware::history($container);
        $handler->push($history);

        $httpClient = new GuzzleHttp\Client(['handler' => $handler]);

        return $httpClient;
    }
    
}
