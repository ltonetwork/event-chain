<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('see that there is an index page');

$I->amOnPage('/');
$I->seeResponseCodeIs(200);

$I->seeElement('a[href="#signup"]');
$I->seeElement('a[href="#login"]');
