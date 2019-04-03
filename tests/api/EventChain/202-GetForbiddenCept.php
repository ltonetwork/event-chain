<?php

/**
 * Get event chain of another user
 * @global $scenario
 */

$I = new ApiTester($scenario);
$I->wantTo('See the error when getting event chain of another user');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$I->sendGET('/event-chains/2bS3mWiDqVxqZEjzCcu1nNDfAJ3bttaWGP9wDVLo59eqeXcGFVP1dcBXiwMUPf');

$I->seeResponseCodeIs(404);
$I->seeResponseContains('Event chain not found');
