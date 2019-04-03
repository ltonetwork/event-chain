<?php

/**
 * Add new events to chain, making sure dispatcher is called
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add events to existing chain, so that dispatcher was called');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$chainId = '2beJbyferseSCtHmByRMK9LGtSY5KaaQYzZUbuWUXGyY76AdPMnPiHnBbqrBtQ';
$oldData = $I->getEntityDump('event-chains', 'different-nodes');
$data = $I->getEntityDump('event-chains', 'different-nodes.append');
$queueFullData = $I->getEntityDump('event-chains', 'different-nodes.full.queued');
$queueAppendedData = $I->getEntityDump('event-chains', 'different-nodes.append.queued');

// bodies, appended event chain was built from
$bodies = [
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '8kda3eac-18ec-496a-8713-8e6e5f012358',
        'node' => 'localhost-3',
        'signkeys' => [
            'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
            'system' => 'CcdaoWD1ZW49ZgMeLJoki2sFZhraZSwHNuweJeEaBZmV'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6',
        'timestamp' => '2019-03-31T21:57:45+0000' // was not included when encoding body, taken from event
    ],
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '2th67uj5-6bd3-4d88-81dd-a6ed933fgv8k',
        'node' => 'localhost',
        'signkeys' => [
            'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6',
        'timestamp' => '2019-03-31T21:57:45+0000' // was not included when encoding body, taken from event
    ],
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => 'w3d6hvb8-6bd3-4d88-81dd-a6ed932s5tg9',
        'node' => 'localhost-4',
        'signkeys' => [
            'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'system' => 'DwqSGapJ295NavPy6wWzmxiR8nSXv3k8zPAoeto6Ct9C'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6',
        'timestamp' => '2019-03-31T21:57:45+0000' // was not included when encoding body, taken from event
    ],
];

// Save first identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies) {    
    $json = json_encode($bodies[0]);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Save second identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies) {    
    $json = json_encode($bodies[1]);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Save third identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies) {    
    $json = json_encode($bodies[2]);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Queue new events to old nodes
$I->expectHttpRequest(function (Request $request) use ($I, $queueAppendedData) {    
    $json = json_encode($queueAppendedData);

    $I->assertEquals('http://event-queuer/queue?to%5B0%5D=localhost-1&to%5B1%5D=localhost-2', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Queue new events to new nodes
$I->expectHttpRequest(function (Request $request) use ($I, $queueFullData) {    
    $json = json_encode($queueFullData);
    
    $I->assertEquals('http://event-queuer/queue?to%5B0%5D=localhost-3&to%5B1%5D=localhost-4', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see whole chain in response');

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $chainId,
    'events' => [
        ['hash' => '6V2EgLMJGatKhdRPZTF9E3CXqx1mqdKUuzVWWVFr56XY'],
        ['hash' => 'CrLJ4UdQDQ4eBtHebL6MPnGs4nSgiHB3TDPrYwQS2VzE'],
        ['hash' => 'Hjhh7CfVRetHqtise36ZrCfXkcZMaJ4GzLwFzHeZFYq8'],
        ['hash' => 'Ge7ZCq7sSs1wvHVEYbGwoqrVqhVhWsvpuV9czsjoMWkK'],
        ['hash' => '8VhY821mx3EQP42nFyBW9pmGUixqUkGFQzspSPXPmxRo'],
        ['hash' => $data['events'][0]['hash']],
        ['hash' => $data['events'][1]['hash']],
        ['hash' => $data['events'][2]['hash']]
    ]
]);

$I->expectTo('get whole saved chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $chainId,
    'events' => [
        ['hash' => '6V2EgLMJGatKhdRPZTF9E3CXqx1mqdKUuzVWWVFr56XY'],
        ['hash' => 'CrLJ4UdQDQ4eBtHebL6MPnGs4nSgiHB3TDPrYwQS2VzE'],
        ['hash' => 'Hjhh7CfVRetHqtise36ZrCfXkcZMaJ4GzLwFzHeZFYq8'],
        ['hash' => 'Ge7ZCq7sSs1wvHVEYbGwoqrVqhVhWsvpuV9czsjoMWkK'],
        ['hash' => '8VhY821mx3EQP42nFyBW9pmGUixqUkGFQzspSPXPmxRo'],
        ['hash' => $data['events'][0]['hash']],
        ['hash' => $data['events'][1]['hash']],
        ['hash' => $data['events'][2]['hash']]
    ]
]);
