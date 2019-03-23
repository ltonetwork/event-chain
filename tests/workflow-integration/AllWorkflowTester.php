<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/_support/TestEventTrait.php';

/**
 * Actions for workflow integration tests
 */
class AllWorkflowTester
{
    use TestEventTrait;

    /**
     * Send post request
     * @param  string $url
     * @param  array $data
     * @return GuzzleHttp\Psr7\Response
     */
    public function sendPost($url, $data)
    {
        $client = new GuzzleHttp\Client();

        $options = [
            'json' => $data, 
            'http_errors' => true,
            'headers' => [
                'Digest' => 'SHA-256=' . base64_encode(hash('sha256', json_encode($data), true))
            ]
        ];

        error_log('SEND: ' . $url . "\n\n" . var_export($options, true));

        return $client->request('POST', $url, $options);
    }

    /**
     * Format query response
     *
     * @param $response
     * @return array
     */
    public function formatResponse($response)
    {
        return [
            'code' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
            'body' => (string)$response->getBody()
        ];
    }
}
