<?php declare(strict_types=1);

require_once __DIR__ . '/header.php';

/**
 * Start a process
 */

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

echo "Stage 1: Create process...\n";

$tester = new AllWorkflowTester();

$chain = $tester->createEventChain(3, $bodies);
$data = $tester->castChainToData($chain);
$response = $tester->sendPost('http://localhost:4000/event-chains', $data);
$formated = $tester->formatResponse($response);
$globalChainId = json_decode($formated['body'])->id;

echo "Request result: {$formated['code']} - {$formated['reason']}; chain id: $globalChainId\n\n";
