<?php

/**
 * Add new events to chain, by passing all chain events
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add events to existing chain, using full chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$bodies = [
    $I->getEntityDump('scenarios', 'basic-user-and-system'),
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#',
        'id' => 'j2134901218ja908323434',
        'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
    ]
];

$chainId = '2buLfKhcnnpQfiiEwHy1GtbJupKWnhGigFPiYbP6QK3tfByHmtKypix1f7M45D';
$chain = $I->getExistingChain($chainId);
$newChain = $I->addEvents($chain, 2, $bodies);
$data = $I->castChainToData($newChain);

// Create scenario at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[0];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][1]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Start process at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[1];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][2]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Send message to process
$I->expectHttpRequest(function (Request $request) use ($I) {
    $json = json_encode(['id' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see whole chain in response');

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $chainId,
    'events' => [
        ['hash' => '8dYYF9vcpKPtvo3isZzWvvAr1uz9fjeZWPwsXBoWhsZ2'],
        ['hash' => $newChain->events[1]->hash],
        ['hash' => $newChain->events[2]->hash]
    ]
]);

$I->expectTo('get whole saved chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $chainId,
    'events' => [
        ['hash' => '8dYYF9vcpKPtvo3isZzWvvAr1uz9fjeZWPwsXBoWhsZ2'],
        ['hash' => $newChain->events[1]->hash],
        ['hash' => $newChain->events[2]->hash]
    ]
]);
