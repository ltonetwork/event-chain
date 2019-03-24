<?php

/**
 * Get not existing event chain
 */

$I = new ApiTester($scenario);
$I->wantTo('See the error when getting not existing event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$I->sendGET('/event-chains/foo');

$I->seeResponseCodeIs(404);
$I->seeResponseContains('Event chain not found');
