<?php

declare(strict_types=1);

use GuzzleHttp\ClientInterface as HttpClient;
use LTO\Account;

/**
 * Service to delete all processes of the workflow engine of current identity
 */
class WorkflowReset
{
    use ResourceService\ExtractFromResponseTrait;
    use ResourceService\InjectEventChainTrait;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var Account
     **/
    protected $node;

    /**
     * Class constructor
     *
     * @param string     $url
     * @param HttpClient $httpClient
     * @param Account    $node
     */
    public function __construct(bool $enabled, string $url, HttpClient $httpClient, Account $node)
    {
        $this->url = $url;
        $this->httpClient = $httpClient;
        $this->node;
    }

    /**
     * See if workflow settings is allowed.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Delete all processes
     *
     * @param string $originalKey
     */
    public function reset(string $originalKey): void
    {
        if (!$this->enabled) {
            throw new BadMethodCallException("Workflow reset not enabled");
        }

        $options = [
            'http_errors' => true,
            'signature_key_id' => base58_encode($this->node->sign->publickey),
            'headers' => [
                'X-Original-Key-Id' => $originalKey,
                'date' => date(DATE_RFC1123)
            ]
        ];

        $this->httpClient->request('DELETE', $this->url, $options);
    }
}
