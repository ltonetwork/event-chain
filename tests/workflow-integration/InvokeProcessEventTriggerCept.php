<?php

/**
 * Create and invoke process, launching event trigger action
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new WorkflowIntegrationTester($scenario);
if ($I->checkIfShouldSkipSuite()) {
    $scenario->skip('Server processes config is not set');
    return;
}

$I->wantTo('Create and invoke process, launching event trigger action');

$data = $I->getEntityDump('event-chains', 'process-event-trigger');
$I->sendPOST('http://localhost:4000/event-chains', $data);

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->seeResponseContainsEventChain();
$I->seeEventChainHasEventsCount(7);

$I->seeEventBodyIs(0, 'identity', '315EKeddeu87qjU7Z4CLvdvQqWRe7YQTNn7fwvvEiN5E6HG9EYJTfDpk33nJ5s');
$I->seeEventBodyIs(1, 'scenario', 'edfbe857-9e0b-4db5-afe9-6bdf5dd1deb0');
$I->seeEventBodyIs(2, 'process', [
    'id' => '2ytBT1p8CTASHoB6iNmFmDvL9fFqk8shxxnrXkbGWfAHCXK3KBDyEKUbFk55UV', 
    'scenario' => 'edfbe857-9e0b-4db5-afe9-6bdf5dd1deb0'
]);
$I->seeEventBodyIs(3, 'response', [
    'process' => '2ytBT1p8CTASHoB6iNmFmDvL9fFqk8shxxnrXkbGWfAHCXK3KBDyEKUbFk55UV',
    'action' => ['key' => 'issue']
]);
$I->seeEventBodyIs(4, 'identity', ['info' => ['name' => 'Waste BV']]);
$I->seeEventBodyIs(5, 'identity', ['info' => ['name' => 'Enforcer']]);
$I->seeEventBodyIs(6, 'response', [
    'process' => '2ytBT1p8CTASHoB6iNmFmDvL9fFqk8shxxnrXkbGWfAHCXK3KBDyEKUbFk55UV', 
    'action' => [ 'key' => 'invite_holder']
]);
