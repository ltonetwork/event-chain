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
$data = $I->getEntityDump('event-chains', 'start-process-basic-system-and-user');
$appendData = $I->getEntityDump('event-chains', 'start-process-basic-system-and-user.append.process-response');

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[0];
    $body['timestamp'] = $data['events'][0]['timestamp'];    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor identity event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "5d6KjzhM5h7LTUSJwnd1RDdsfu64Kvia4dKBiniBv9LG", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    return new Response(200);
});

// Create scenario at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[1];
    $body['timestamp'] = $data['events'][1]['timestamp'];    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor scenario event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "6N6JVJc4H2DPXCR5Jxhitjnh32WkvYzUvt1f2KoFQcMr", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Start process at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[2];
    $body['timestamp'] = $data['events'][2]['timestamp'];    

    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringContainsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor process event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "H9yPhvTKfPDWKHKe7W2kg3qMq5uk5qhNFzxW29qrHHEb", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
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
    $body['timestamp'] = $appendData['events'][0]['timestamp'];    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor first response event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "6RYb1fACZ4crtd4APZe2iD29tdETv4rmCvuFp8t2ZVGg", "encoding": "base58"}';
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
    $body['timestamp'] = $appendData['events'][1]['timestamp'];    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor second response event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "DE8Bx3yc8daVi21WFVzmYStdmtXHi51bAKrfjU7Wyu6o", "encoding": "base58"}';
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
    $body['timestamp'] = $appendData['events'][2]['timestamp'];    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/response', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor third response event
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "9Bg32zZZjeXVts5nFPfJGQ5TiYdSXmvq52jmwH4D8zy1", "encoding": "base58"}';
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
$I->seeResponseIsEventChain('start-process-basic-system-and-user.full.process-response');

$I->expectTo('obtain saved chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain('start-process-basic-system-and-user.full.process-response', ['latest_hash']);
