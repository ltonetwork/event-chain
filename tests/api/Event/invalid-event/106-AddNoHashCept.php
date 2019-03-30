<?php

/**
 * Try adding event with no hash
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding event with no hash');

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
unset($data['events'][0]['hash']);

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $body0) {
    $json = json_encode($body0);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains("event '': hash is required");

$I->expectTo('see that chain was not saved');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(404);
$I->seeResponseEquals('Event chain not found');
