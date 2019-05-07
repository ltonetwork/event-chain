<?php

/**
 * Try adding event with no signkey attribute
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding event with no signkey attribute');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

// body of first event
$body0 = [
    '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
    'id' => '0c1d7eac-18ec-496a-8713-8e6e5f098686',
    'node' => 'localhost',
    'signkeys' => [
        'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
        'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
    ],
    'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6',
    'timestamp' => '2019-03-30T19:10:43+0000' // is not present in encoded body, taken from event timestamp
];

$data = $I->getEntityDump('event-chains', 'only-identities');
$chainId = $data['id'];
unset($data['events'][1]['signkey']);

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $body0) {
    $json = json_encode($body0);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Anchor identity event
$I->expectHttpRequest(function (Request $request) use ($I, $data) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "Hm7W4Kprv52vfXoYmdG6Ee3pso6ruszaCLfJDxFotGjn", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    return new Response(200);
});

// Anchor error event
$I->expectHttpRequest(function (Request $request) use ($I, $data) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"encoding": "base58"}';
    $I->assertJsonStringContainsJsonString($json, (string)$request->getBody());

    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$errors = [
    "event 'BuizdWTtk7A6Xrt71i8Fy1npwE8x5KUVP4Q82xFGFFHy': signkey is required",
    "event 'BuizdWTtk7A6Xrt71i8Fy1npwE8x5KUVP4Q82xFGFFHy': invalid signature",
    "event 'BuizdWTtk7A6Xrt71i8Fy1npwE8x5KUVP4Q82xFGFFHy': invalid hash"
];

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContainsJson($errors);

$I->expectTo('see that error event was added to event chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['events' => ['hash' => $data['events'][0]['hash']]]);
$I->dontSeeResponseContainsJson(['events' => ['hash' => $data['events'][1]['hash']]]);
$I->dontSeeResponseContainsJson(['events' => ['hash' => $data['events'][2]['hash']]]);

$data['events'][1]['signkey'] = null;

$I->seeValidErrorEventInResponse(
    $errors, 
    [$data['events'][1], $data['events'][2]]
);
