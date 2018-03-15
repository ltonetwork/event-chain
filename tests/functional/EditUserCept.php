<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('edit my user info');

$I->amLoggedInAs('test-user@example.com');
$I->amOnPage('/settings');
$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/settings');

$form = 'form[action="/settings"]';

$I->seeElement('.avatar-block');
$I->seeElement('.social-buttons');
$I->seeElement('.delete-user-button');
$I->seeElement($form);
$I->seeInField($form . ' [name="first_name"]', 'John');
$I->seeInField($form . ' [name="last_name"]', 'Doe');
$I->seeInField($form . ' [name="email"]', 'test-user@example.com');

$I->submitForm($form, [
    'first_name' => 'John 2',
    'last_name' => 'Doe 2',
    'email' => 'test-changed@example.com'
]);

$I->seeCurrentUrlEquals('/settings');
$I->seeResponseCodeIs(200);
$I->see("User info is saved", '.alert-fixed-top');

$I->seeElement($form);
$I->seeInField($form . ' [name="first_name"]', 'John 2');
$I->seeInField($form . ' [name="last_name"]', 'Doe 2');
$I->seeInField($form . ' [name="email"]', 'test-changed@example.com');

$I->amLoggedOut();
