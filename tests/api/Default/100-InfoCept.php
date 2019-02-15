<?php

$I = new ApiTester($scenario);
$I->wantTo('tests the system info response');

$I->sendGET('/');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->seeResponseContainsJson([
    'name' => 'lto/eventchain',
    'description' => 'LTO Network - Event chain service',
    'env' => 'tests',
    'signkey' => App::getContainer()->get('node.account')->getPublicSignKey(),
]);
