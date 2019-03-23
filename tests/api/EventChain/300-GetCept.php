<?php

/**
 * Get existing event chain
 */

$I = new ApiTester($scenario);
$I->wantTo('Get existing event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$I->sendGET('/event-chains/CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain('single-event');
