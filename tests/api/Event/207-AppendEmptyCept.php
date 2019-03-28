<?php

/**
 * Try adding empty events set to existing chain
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try sending empty event chain for adding to existing chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$data = [
    'id' => '2buLfKhcnnpQfiiEwHy1GtbJupKWnhGigFPiYbP6QK3tfByHmtKypix1f7M45D',
    'events' => [],
    'identities' => [],
    'resources' => []
];

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains('no events');
