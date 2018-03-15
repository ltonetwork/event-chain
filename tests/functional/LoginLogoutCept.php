<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('login and logout');

$I->amOnPage('/');
$I->seeResponseCodeIs(200);

$form = '#login form';
$I->seeElement($form);

$I->submitForm($form, [
    'email' => 'test-user@example.com',
    'password' => 'password'
]);

$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/');
$I->seeElement('a[href="/settings"]');
$I->see("Hi John Doe, welcome!", '.alert-fixed-top');

$I->seeElement('a[href="/logout"]');
$I->click('a[href="/logout"]');

$I->seeResponseCodeIs(200);
$I->dontSeeElement('a[href="/settings"]');
$I->seeCurrentUrlEquals('/');
