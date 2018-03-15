<?php 
$I = new FunctionalTester($scenario);
$I->am('John Doe');
$I->wantTo('reset my password');

$I->amOnPage('/');
$I->seeResponseCodeIs(200);

$form = '#forgot-password form';
$I->seeElement($form);

$I->submitForm($form, [
    'email' => 'test-user@example.com'
]);


$I->expectTo("receive a password reset mail");

$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/');

$I->see("An e-mail with link for reseting password is on it's way", '.alert-fixed-top');

$I->seeEmailIsSend('reset-password.html.twig', ["Set new password"]);
$resetLink = $I->grabAttributeFromEmail('reset-password.html.twig', 'a.reset-link', 'href');


$I->expectTo("be able to reset my password by following the reset link");
$I->amOnPage($resetLink);

$form = 'form#reset-password-form';
$I->seeElement($form);

$I->submitForm($form, [
    'password' => 'abcde09876',
    'password_confirm'  => 'abcde09876'
]);

$I->expectTo("be logged in as John Doe");

$I->seeCurrentUrlEquals('/');
$I->see("Password has been reset successfully", '.alert-fixed-top');
$I->seeElement('a[href="/settings"]');


$I->expectTo("be able to log in with my new password");

$I->amLoggedOut();
$I->amOnPage('/');

$form = '#login form';
$I->seeElement($form);

$I->submitForm($form, [
    'email' => 'test-user@example.com',
    'password' => 'abcde09876'
]);

$I->seeCurrentUrlEquals('/');
$I->see("Hi John Doe, welcome!", '.alert-fixed-top');

$I->amLoggedOut();
