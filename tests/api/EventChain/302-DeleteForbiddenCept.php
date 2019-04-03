<?php

/**
 * Try to delete event chain of another user
 * @global $scenario
 */

$I = new ApiTester($scenario);
$I->wantTo('see error when deleting event chain of another user');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$I->sendDELETE('/event-chains/2bS3mWiDqVxqZEjzCcu1nNDfAJ3bttaWGP9wDVLo59eqeXcGFVP1dcBXiwMUPf');
$I->seeResponseCodeIs(404);
