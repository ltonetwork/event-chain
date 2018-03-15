<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('delete my user account');

$I->amLoggedInAs('test-user@example.com');
$I->sendPOST('/settings/delete');

$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/good-bye');

$I->amOnPage('/');
$I->dontSeeElement('a[href="/settings"]');
