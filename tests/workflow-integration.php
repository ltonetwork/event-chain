<?php declare(strict_types=1);

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';
require_once 'tests/_support/TestEventTrait.php';

App::init();

class AllWorkflowTester
{
    use TestEventTrait;

    /**
     * Helper method, when creating test events' bodies
     * @param  string $body  Encoded event body
     * @return stdClass
     */
    public function decodeEventBody($body): stdClass
    {
        $data = base58_decode($body);        

        return json_decode($data);
    }

    /**
     * Get chain data to send in request
     * @param  EventChain $chain
     * @return stdClass
     */
    public function castChainToData(EventChain $chain): stdClass
    {
        return json_decode(json_encode($chain));
    }

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

$scenario = file_get_contents('tests/_data/scenarios/basic-user-and-system.json');
$scenario = json_decode($scenario);

$bodies = [
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '0c1d7eac-18ec-496a-8713-8e6e5f098686',
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'node' => 'localhost',
        'privileges' => null,
        'signkeys' => [
            'user' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
            'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
    ],
    (array)$scenario,
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#',
        'id' => 'j2134901218ja908323434',
        'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
    ]
];

$tester = new AllWorkflowTester();

$chain = $tester->createEventChain(3, $bodies);
$data = $tester->castChainToData($chain);
$response = $tester->sendPost('http://localhost:4000/event-chains', $data);
$formated = $tester->formatResponse($response);

var_export($formated);
