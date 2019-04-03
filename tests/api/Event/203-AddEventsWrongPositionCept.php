<?php

/**
 * Try adding events, that are wrongly positioned
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try creating event chain with wrongly positioned events');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$chainId = '2bdKHeRWy2hgfdh7mctaz1fDGWWSs5SSG2qAmkSJnQyaKCds7WrZWXpi7SbZ6S';
$data = $I->getEntityDump('event-chains', $chainId);

$temp = $data['events'][1];
$data['events'][1] = $data['events'][2];
$data['events'][2] = $temp;

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains("broken chain; previous of '5GTif9KqLu6hcLHpUAgczTD3NcCaEhwJkyumJhoZid93' is 'GWw8PfFNx5HM3LvMAjn11TnjQAuC1W3882MphJyF7dKP', expected '95XoV7gfjQ1cpmuUqNvbrheP7vjs6g6ZmTB8Lcpdf1AQ'");

$I->expectTo('see that chain was not saved');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(404);
$I->seeResponseContains("Event chain not found");
