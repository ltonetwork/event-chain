<?php

/**
 * Add new chain to the queue
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain to the queue');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$bodies = [
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '0c1d7eac-18ec-496a-8713-8e6e5f098686',
        'node' => 'localhost',
        'signkeys' => [
            'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
            'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
    ],
    $I->getEntityDump('scenarios', 'basic-user-and-system'),
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#',
        'id' => 'j2134901218ja908323434',
        'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
    ]
];

$chain = $I->createEventChain(3, $bodies);
$data = $I->castChainToData($chain);

// Get node
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://event-queuer/', (string)$request->getUri());
    $I->assertEquals('GET', $request->getMethod());
    
    return new Response(200, [], json_encode(['node' => 'node1']));
});

// Dispatch existing nodes
$I->expectHttpRequest(function (Request $request) use ($I, $data) {
    $json = json_encode($data);

    $I->assertEquals('http://event-queuer/queue', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(204);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/queue', $data);

$I->expectTo('see successfull response "no content"');

$I->dontSee("broken chain");
$I->seeResponseCodeIs(204);

$I->dontSeeInCollection('event_chains', ["_id" => $chain->id]);
