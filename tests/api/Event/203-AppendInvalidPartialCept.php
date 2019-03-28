<?php

/**
 * Try add events, using partial chain, if first one reference event, that does not exists in chain
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding partial chain, that references not-existing previous event');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$chainId = '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD';
$data = $I->getEntityDump('event-chains', $chainId . '.fork-all');
array_shift($data['events']);

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message in response');

$I->dontSee("broken chain");
$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains("events don't fit on chain, '7eYPtGT1i5fai1v5UAQft5bcFzj6DDEQLqeWBiNm7MFe' not found");

$I->expectTo('get whole chain and see if error event was saved');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$lastEvent = $I->seeValidErrorEventInResponse(
    ["events don't fit on chain, '7eYPtGT1i5fai1v5UAQft5bcFzj6DDEQLqeWBiNm7MFe' not found"],
    $data['events']    
);
