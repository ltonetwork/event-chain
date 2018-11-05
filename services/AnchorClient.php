<?php declare(strict_types=1);

use Improved as i;
use Improved\Iterator\CombineIterator;
use Improved\IteratorPipeline\Pipeline;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class to interact with anchor service.
 */
class AnchorClient
{
    /**
     * @var stdClass
     */
    protected $config;

    /**
     * @var HttpClient
     */
    protected $httpClient;
    
    
    /**
     * Class constructor
     *
     * @param \stdClass|array $config
     * @param HttpClient      $httpClient
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
    public function submit(string $hash, string $encoding = 'base58'): void
    {
        $url = "{$this->config->url}/hash";

        $options = [
            'json' => compact('hash', 'encoding'),
            'http_errors' => true,
            'query' => []
        ];
        
        $this->httpClient->request('POST', $url, $options);
    }


    /**
     * Fetch anchor information.
     *
     * @param string $hash
     * @param string $encoding
     * @return \stdClass|null
     */
    public function fetch(string $hash, string $encoding = 'base58'): ?stdClass
    {
        return $this->fetchMultiple([$hash], $encoding)->first();
    }

    /**
     * Fetch anchor information for multiple hashes.
     * If a hash isn't anchored it's omitted from the result.
     *
     * @param iterable<string> $hashes
     * @param string           $encoding
     * @return Pipeline&iterable<string, \stdClass>
     * @throws RequestException
     * @throws UnexpectedValueException
     */
    public function fetchMultiple(iterable $hashes, string $encoding = 'base58'): Pipeline
    {
        return Pipeline::with($hashes)
            ->mapKeys(function(string $hash) use ($encoding) {
                return "{$this->config->url}/hash/$hash/encoding/$encoding";
            })
            ->map(function(string $hash, string $url) {
                return $this->httpClient->requestAsync('GET', $url, ['http_errors' => true]);
            })
            ->then(function(iterable $iterable) {
                // Keys may not be scalar, so we need to separate to get promises array.
                ['keys' => $keys, 'promises' => $promises] = i\iterable_separate($iterable);

                $responses = Promise\unwrap($promises);
                ksort($responses, SORT_NUMERIC);

                return new CombineIterator($keys, $responses);
            })
            ->filter(function(Response $response) {
                return $response->getStatusCode() !== 404;
            })
            ->apply(function(Response $response, string $url) {
                if ($response->getStatusCode() >= 400) {
                    $request = new Request('GET', $url);
                    throw RequestException::create($request, $response);
                }
            })
            ->map(Closure::fromCallable([$this, 'decodeBody']));
    }

    /**
     * Decode a JSON encoded object from an HTTP response body.
     *
     * @param Response $response
     * @param string   $url
     * @return stdClass
     */
    protected function decodeBody(Response $response, string $url): stdClass
    {
        $result = json_decode($response->getBody());

        if (json_last_error() > 0) {
            $err = json_last_error_msg();
            throw new UnexpectedValueException("Failed to decode body as JSON for '$url': $err");
        }

        return i\type_check(
            $result,
            stdClass::class,
            new UnexpectedValueException("Expected response for '$url' to be an object, got %s")
        );
    }
}
