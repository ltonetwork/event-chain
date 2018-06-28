<?php

$I = new ApiTester($scenario);
$I->wantTo('get all event chains of user');

$I->amSignatureAuthenticated("PIw+8VW129YY/6tRfThI3ZA0VygH4cYWxIayUZbdA3I9CKUdmqttvVZvOXN5BX2Z9jfO3f1vD1/R2jxwd3BHBw==");

$I->sendGET('/event-chains');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->seeResponseContainsJson([
    ["id" => "2bS3mWiDqVxqZEjzCcu1nNDfAJ3bttaWGP9wDVLo59eqeXcGFVP1dcBXiwMUPf"]
]);

$I->dontSeeResponseContainsJson([
    ["id"  => "CuG8MCUgM4GRteAcPT4fntnv27UdoZQwEhavozosxri62"]
]);