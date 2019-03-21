<?php declare(strict_types=1);

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/start-process.php';

/**
 * Update (replace) existing identity
 */

$bodies = [
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '0c1d7eac-18ec-496a-8713-8e6e5f098686',
        'node' => 'localhost',
        'signkeys' => [
            'default' => '57FWtEbXoMKXj71FT84hcvCxN5z1CztbZ8UYJ2J49Gcn', 
            'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ]
    ]
];

echo "Stage 2: Replace an identity...\n";

$tester = new AllWorkflowTester();

$chain = $tester->getExistingChain($globalChainId);
$chain = $tester->addEvents($chain, 1, $bodies);

$data = $tester->castChainToData($chain);
$response = $tester->sendPost('http://localhost:4000/event-chains', $data);
$formated = $tester->formatResponse($response);

echo "Request result: {$formated['code']} - {$formated['reason']}\n\n";
