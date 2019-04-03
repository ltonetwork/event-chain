<?php

/**
 * Queue event chain, that has wrongly positioned events
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try to queue event chain, that has wrongly positioned events');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$data = $I->getEntityDump('event-chains', '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD');

$temp = $data['events'][1];
$data['events'][1] = $data['events'][2];
$data['events'][2] = $temp;

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/queue', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains("broken chain; previous of 'J4pM5KNkrzeBb8233uFCq1tVRGN4LQ3SVNyDGU3Ys2Jw' is 'HMVbGHtcYm6DTciYAhkJkzDUnW1j1Mqo29X37rir5ufo', expected 'HqDutMxVv6PkLFgGWz8wByaEgSuS3R1zcGeNKW6hSFST'");
