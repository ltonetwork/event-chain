<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('change password');

$I->amLoggedInAs('test-user@example.com');

$I->amOnPage('/settings/edit-password');
$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/settings/edit-password');

$form = 'form[action="/settings/edit-password"]';
$I->seeElement($form);

$I->submitForm($form, [
    'old_password' => 'password',
    'password' => 'password-changed',
    'password_confirm' => 'password-changed'
]);

$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/settings');
$I->see("New password is set", '.alert-fixed-top');

$I->amLoggedOut();
