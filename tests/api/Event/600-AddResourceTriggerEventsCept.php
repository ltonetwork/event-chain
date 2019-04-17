<?php

/**
 * Save new event chain
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain, so that it contained events, obtained when triggering resources');

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
    ],
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
    ]
];

$chainId = '2bPHYGN2546YwkmVzP8aWMHqCzdPp7TUBy8tf3PB9DJShnPY6YrHqAyQDH4eBi';
$data = $I->getEntityDump('event-chains', $chainId);
$appendData = $I->getEntityDump('event-chains', $chainId . '.append.invoke-process');

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[0];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][0]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Create scenario at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[1];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][1]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Start process at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[2];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][2]);    
    $body['chain'] = [
        'id' => $data['id'],
        'events' => [],
        'identities' => [],
        'resources' => []
    ];

    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringContainsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Send message to process. In response we obtain an event, containing first process' response
$I->expectHttpRequest(function (Request $request) use ($I, $appendData) {
    $json = json_encode(['id' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    $responseChain = [
        'id' => $appendData['id'],
        'events' => [$appendData['events'][0]]
    ];
    
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($responseChain));
});

// Save first response at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $appendData) {
    $body = $bodies[3];
    $body['timestamp'] = $I->getTimeFromEvent($appendData['events'][0]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// // Send message to process. In response we obtain an event, containing second process' response
$I->expectHttpRequest(function (Request $request) use ($I, $appendData) {
    $json = json_encode(['process' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    $responseChain = [
        'id' => $appendData['id'],
        'events' => [$appendData['events'][1]]
    ];
    
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($responseChain));
});

// // Save second response at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $appendData) {
    $body = $bodies[4];
    $body['timestamp'] = $I->getTimeFromEvent($appendData['events'][1]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// // Send message to process. In response we obtain an event, containing third process' response
$I->expectHttpRequest(function (Request $request) use ($I, $appendData) {
    $json = json_encode(['process' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    $responseChain = [
        'id' => $appendData['id'],
        'events' => [$appendData['events'][2]]
    ];
    
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($responseChain));
});

// // Save third response at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $appendData) {
    $body = $bodies[5];
    $body['timestamp'] = $I->getTimeFromEvent($appendData['events'][2]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// // Send message to process. All actions are done, so no data is returned
$I->expectHttpRequest(function (Request $request) use ($I, $appendData) {
    $json = json_encode(['process' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(204);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see chain in response');

$I->dontSee('broken chain');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId . '.full.invoke-process');

$I->expectTo('obtain saved chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId . '.full.invoke-process');
