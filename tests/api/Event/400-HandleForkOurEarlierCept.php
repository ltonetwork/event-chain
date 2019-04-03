<?php

/**
 * Process two forks, if our fork is earlier. Rebase should not be performed, chain should remain unchanged
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('process two forks of the same chain, if *our* fork is earlier');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$I->expectTo('obtain valid fork');

$chainId = '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD';
$chain = $I->getEntityDump('event-chains', $chainId);
$fork = $I->getEntityDump('event-chains', "$chainId.fork-1-event");

// Request hash position at blockchain of *our* event hash, first parallel to fork
$I->expectHttpRequest(function (Request $request) use ($I, $chain) {
    $hash = $chain['events'][1]['hash'];
    $I->assertEquals("http://anchor/hash/$hash/encoding/base58", (string)$request->getUri());

    $response = [
        'block' => ['height' => 2],
        'transaction' => ['position' => 1]
    ];
    
    return new Response(200, [], json_encode($response));
});

// Request hash position at blockchain of *their* event hash, first hash of fork
$I->expectHttpRequest(function (Request $request) use ($I, $fork) {
    $hash = $fork['events'][1]['hash']; 
    $I->assertEquals("http://anchor/hash/$hash/encoding/base58", (string)$request->getUri());

    $response = [
        'block' => ['height' => 2],
        'transaction' => ['position' => 3]
    ];
    
    return new Response(200, [], json_encode($response));
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $fork);

$I->expectTo('see in response, that chain did not change');

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId);

$I->expectTo('get whole chain and see, that it did not change');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId);
