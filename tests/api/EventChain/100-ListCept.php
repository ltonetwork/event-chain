<?php

$I = new ApiTester($scenario);
$I->wantTo('get all event chains of user');

$I->haveHttpHeader($name, $value);

$I->sendGET('/event-chains');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->responseToHttpRequestWith(new Response(200));

