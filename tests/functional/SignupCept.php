<?php
$I = new FunctionalTester($scenario);
$I->wantTo('sign up');

$I->amOnPage('/');
$I->seeResponseCodeIs(200);

$form = '#signup form';
$I->seeElement($form);

$I->submitForm($form, [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'test@example.com',
    'password' => 'some-password'
]);

$I->seeResponseCodeIs(200);

$user = $I->grabFromCollection('users', ['email' => 'test@example.com']);
$I->seeInCollection('users', [            
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'test@example.com',
    'access_level' => 1,
    'active' => false
]);

$I->seeCurrentUrlEquals('/');

$I->see("Hi, John Doe, welcom! We've send you an email to complete registration.", '.alert-fixed-top');

$I->seeEmailIsSend('signup.html.twig', ["Confirm your registration"]);
$confirmLink = $I->grabAttributeFromEmail('signup.html.twig', 'a.signup-link', 'href');

$I->expect('to confirm registration');

$I->amOnPage($confirmLink);
$I->seeResponseCodeIs(200);
$I->seeCurrentUrlEquals('/');
$I->see("Your email is verified", '.alert-fixed-top');

$I->seeInCollection('users', [            
    '_id' => $user['_id'],
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'test@example.com',
    'access_level' => 1,
    'active' => true
]);