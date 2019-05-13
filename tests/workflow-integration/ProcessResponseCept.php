<?php

/**
 * Send a response to process, up to successfull ending
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new WorkflowIntegrationTester($scenario);
if ($I->checkIfShouldSkipSuite()) {
    $scenario->skip('Server processes config is not set');
    return;
}

$I->wantTo('Create and step through the process, up to it\'s successfull ending');

$data = $I->getEntityDump('event-chains', 'start-process-basic-system-and-user.full.process-response');
$I->sendPOST('http://localhost:4000/event-chains', $data);

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$I->seeResponseContainsEventChain();
$I->seeEventChainHasEventsCount(6);

$I->seeEventBodyIs(0, 'identity', '0c1d7eac-18ec-496a-8713-8e6e5f098686');
$I->seeEventBodyIs(1, 'scenario', '2557288f-108e-4398-8d2d-7914ffd93150');
$I->seeEventBodyIs(2, 'process', [
    'id' => 'j2134901218ja908323434', 
    'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
]);
$I->seeEventBodyIs(3, 'response', [
    'process' => 'j2134901218ja908323434',
    'action' => 'step1'
]);
$I->seeEventBodyIs(4, 'response', [
    'process' => 'j2134901218ja908323434',
    'action' => 'step2'
]);
$I->seeEventBodyIs(5, 'response', [
    'process' => 'j2134901218ja908323434',
    'action' => 'step3'
]);

$I->expectTo('see that process is finished successfully');
$I->sendGET('http://localhost:4001/processes/j2134901218ja908323434');

$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => 'j2134901218ja908323434',
    'state' => ':success'
]);
