<?php

/**
 * Delete not existing event chain
 */

$I = new ApiTester($scenario);
$I->wantTo('see error when deleting not existing event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$id = 'foo';

$I->expectTo('see that chain not exists');

$I->sendGET('/event-chains/' . $id);
$I->seeResponseCodeIs(404);
$I->seeResponseContains('Event chain not found');

$I->expectTo('get an error when deleting the chain');

$I->sendDELETE('/event-chains/' . $id);
$I->seeResponseCodeIs(404);
