<?php declare(strict_types=1);

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/start-process.php';

/**
 * Send a response to process, up to successfull ending
 */

$bodies = [
    [ // process goes from state ':initial' to 'second'
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/response/schema.json#',
        'action' => 'step1',
        'key' => 'ok',
        'actor' => 'system',
        'process' => 'j2134901218ja908323434',
        'data' => ['foo' => 'bar']
    ],
    [ // from state 'second' to 'third'
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/response/schema.json#',
        'action' => 'step2',
        'key' => 'ok',
        'actor' => 'system',
        'process' => 'j2134901218ja908323434',
        'data' => ['foo' => 'bar']
    ],
    [ // from state 'third' to ':success'
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/response/schema.json#',
        'action' => 'step3',
        'key' => 'ok',
        'actor' => 'system',
        'process' => 'j2134901218ja908323434',
        'data' => ['foo' => 'bar']
    ],
];

echo "Stage 2: Successfully step through the process...\n";

$tester = new AllWorkflowTester();

$chain = $tester->getExistingChain($globalChainId);
$chain = $tester->addEvents($chain, 3, $bodies);

$data = $tester->castChainToData($chain);
$response = $tester->sendPost('http://localhost:4000/event-chains', $data);
$formated = $tester->formatResponse($response);

echo "Request result: {$formated['code']} - {$formated['reason']}\n\n";
