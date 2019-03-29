<?php

/**
 * Try adding some of existing events again
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding some of existing chain events again');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$chainId = '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD';
$data = $I->getEntityDump('event-chains', $chainId);
array_shift($data['events']);

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see unchaged chain in response');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId);

$I->expectTo('get whole chain and see if it did not change');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->seeResponseIsEventChain($chainId);
