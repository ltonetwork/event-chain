<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add events to existing chain, using partial chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$scenario = file_get_contents('tests/_data/scenarios/basic-user-and-system.json');
$scenario = json_decode($scenario, true);

$bodies = [
    $scenario,
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#',
        'id' => 'j2134901218ja908323434',
        'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
    ]
];

$chain = $I->getExistingChain('CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62');
$newChain = $I->addEvents($chain, 2, $bodies, true);
$data = $I->castChainToData($newChain);

// Create scenario at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[0];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][0]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Start process at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[1];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][1]);    
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
    'id' => 'CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62',
    'events' => [
        ['hash' => 'J8pQ8k52riGXPt8HeataGbSLdzLTESrGBZZKtAWrspHb'],
        ['hash' => $newChain->events[0]->hash],
        ['hash' => $newChain->events[1]->hash]
    ]
]);

$I->expectTo('get whole saved chain');

$I->sendGET('/event-chains/CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => 'CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62',
    'events' => [
        ['hash' => 'J8pQ8k52riGXPt8HeataGbSLdzLTESrGBZZKtAWrspHb'],
        ['hash' => $newChain->events[0]->hash],
        ['hash' => $newChain->events[1]->hash]
    ]
]);

