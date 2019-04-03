<?php

/**
 * Add an empty chain to the queue
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding an empty event chain to the queue');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$data = [
    'id' => '2au5R3nt4qGdV7E7qUudBtG67hqPMqLtrYGpUxFKxTbggwVAVBjWLRFrhsbAJb',
    'events' => [],
    'identities' => [],
    'resources' => []
];

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/queue', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains('no events');
