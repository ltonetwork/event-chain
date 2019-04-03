<?php

/**
 * Try adding event with invalid original to existing chain
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding event with invalid original to existing chain');

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
    'timestamp' => '2019-03-30T22:38:03+0000' // is not present in encoded body, taken from event timestamp
];

$data = $I->getEntityDump('event-chains', '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD.append');
$chainId = $data['id'];
$data['events'][1]['original'] = 'foo';

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $body0) {
    $json = json_encode($body0);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$errors = [
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': invalid signature",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': invalid hash",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; body is required",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; timestamp is required",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; previous is required",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; signkey is required",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; signature is required",
    "event 'HNYfjH39pUgo6d2ovbVoEmu2REmedsRAYAy6UxeuoKX6': original event; hash is required"
];

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContainsJson($errors);

$I->expectTo('see that error event was added to event chain');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

// Existing events
$I->seeResponseContainsJson(['events' => ['hash' => 'HqDutMxVv6PkLFgGWz8wByaEgSuS3R1zcGeNKW6hSFST']]);
$I->seeResponseContainsJson(['events' => ['hash' => 'HMVbGHtcYm6DTciYAhkJkzDUnW1j1Mqo29X37rir5ufo']]);
$I->seeResponseContainsJson(['events' => ['hash' => 'J4pM5KNkrzeBb8233uFCq1tVRGN4LQ3SVNyDGU3Ys2Jw']]);

$I->seeResponseContainsJson(['events' => ['hash' => $data['events'][0]['hash']]]);
$I->dontSeeResponseContainsJson(['events' => ['hash' => $data['events'][1]['hash']]]);
$I->dontSeeResponseContainsJson(['events' => ['hash' => $data['events'][2]['hash']]]);

$data['events'][1]['original'] = [
    'origin' => null,
    'body' => null,
    'timestamp' => null,
    'previous' => null,
    'signkey' => null,
    'signature' => null,
    'hash' => null,
    'receipt' => null,
    'original' => null
];

$I->seeValidErrorEventInResponse(
    $errors, 
    [$data['events'][1], $data['events'][2]]
);
