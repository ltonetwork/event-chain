<?php

/**
 * Delete existing event chain
 */

$I = new ApiTester($scenario);
$I->wantTo('delete existing event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$id = 'CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62';

$I->expectTo('see that chain exists');

$I->sendGET('/event-chains/' . $id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['id' => $id]);

$I->expectTo('delete the chain');

$I->sendDELETE('/event-chains/' . $id);
$I->seeResponseCodeIs(204);

$I->expectTo('see that chain does not exists anymore');

$I->sendGET('/event-chains/' . $id);
$I->seeResponseCodeIs(404);
