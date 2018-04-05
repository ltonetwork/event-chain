<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$data = [
    "id" => "CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f",
    "events" => [
        [
            "timestamp" => "1522425700",
            "body" => "2D5Gw6o78SjNqL2L3FPainPnNyeuHUwbck7v75u3KmWsUGbgGQuHya2Zu9sWbqJ8STuVrkWN1WwTVGE8hKuC6zXMJ2j2JJntQv3G8EYUZpHUeFYFML8RUS1drQw9ufMhF4K644NrLmpJ1ioiccUhewpspPWe8AhCJ2VYVaMUtmcjF95f9RMpWxgPsYX4Wn92rPHeEnM8oX9bFnZBhoh2v1HRDJwmgHnvhU6Lukc8DyCgHwXaR6rBCqwidDsQZJTGdn6LQNJTBVmEYuK1o7DK6Kysvx4nAuaQW5R21SaELtupLaSefnZuUC9LtsLELQDnzz9VgKXy8zUG7ZT6QtwbMEdXCzE5GFTFcMthcuN9PcdQWKY3VQP9o3ewQFJZ5JLWCLq17UYW7EYoT39CEZwttkX1vgDDdcKL4zrLFo5JduqfSVqQ72z4J8j8UQPdbQTNodDWhjKz1EBXa",
            "previous" => "7juAGSAfJJ2Th9SXGpm3u9XcLtMZzFaExbnCrnUAi1kn",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "MR5XNoQsv7Li6oEUyZNBKJRcs1rn7VyymrxEdppfrWQqFVx8jMNL213R1XSpRvvb8m7awvNqH2QfWAeGwZXa1cS",
            "hash" => "3UUnVFXTPXnvSpP5yvZAbVsED6B1W2SNNirSbqShvofY"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "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",
            "previous" => "3UUnVFXTPXnvSpP5yvZAbVsED6B1W2SNNirSbqShvofY",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "2EcdkQLMQtwPyyLsFrVXcEe2U7sD2bLxmgViqKbyzyepB1CfZ4L2maBowk7sZauXVm7c1AvfuvGTwxkP2wdpJ1JJ",
            "hash" => "6d4uy3G5LK2jkLudo6RK79KeHzuCi7P9RH1qgwbRxtxd"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "2SwokuWCLbjvuatPFWpzQgbpEYZLGpnjfj19eMtSibTaLfEcW3R3CHcNoJqKqZCr69oQ2dfL9k8qcDh6mnpf37atsBLGT4akpKf7HCr2exhGyNwpr6Vo2cXAmCHqPTAMdbRUFBTmefgxGD7MggUQdudc5TAVJGNjNTGuJuzEaZJV7XL5k5pdkSDdhTgK62suhWemZV8Rvf7qTVjnYhEQ3HWwAQoaHzZrZEZxVoWX9LzR61DaCEGjTux5GFf5qB1d6i7GygNaidLWxHXGcygLtyS8Dbqi6Qx6iekzAc18yosGemZBVscTsFEGYTAFVfm9HsdyFutxS6JMLQLAjdwYFeSXxwkGywumDq38hDpZP892oyzCuhvjkY6YwQKgLMXUYsF5fxYaznNcCghygbnApPE2EYjZ79ixLvpRvWHMBUR3Dx9XQr8RpWs2hmzdErMDD6wbbSAEXe9oemr2Atncg53jNKwbEBPvwk1g2XLHTWL",
            "previous" => "6d4uy3G5LK2jkLudo6RK79KeHzuCi7P9RH1qgwbRxtxd",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "4qw41coUs4YFVTVHx3pFGW4j9fqN3heg5UaMuLNQ2akBbquj9V4pNqdZUJGPV4TKtpDEfctqgm1AehfvMtEtzQ4Y",
            "hash" => "8xJMEqxj1yUqqvHZwhfec8VPvSs1XRXWFQq1ZafEFAsR"
        ]
    ]
];

// Scenario
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    
    $json = '{"$schema":"http://specs.livecontracts.io/draft-01/04-scenario/schema.json#","id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=9zUysCou","name":"Accept or reject a quotation","description":"Accept or reject a quotation","keywords":[],"tags":[],"info":{},"assets":{"request":{"type":"object","properties":{"description":{"type":"string"},"urgency":{"type":"string","enum":["normal","high","critical"]}}},"quotation":{"$ref":"http://specs.livecontracts.io/draft-01/10-document/schema.json#"}},"actors":{"supplier":{"key":"supplier","title":"","description":""},"client":{"key":"client","title":"","description":""}},"actions":{"request_quotation":{"key":"request_quotation","title":"","description":"","actor":"client","date":"","hash":"","form":{"<ref>":"definitions.request_form"}},"invite_supplier":{"key":"invite_supplier","title":"","description":"","actor":"client","date":"","hash":""},"enter_client":{"key":"enter_client","title":"","description":"","actor":"supplier","date":"","hash":"","form":{"<ref>":"definitions.request_form"}},"invite_client":{"key":"invite_client","title":"","description":"","actor":"supplier","date":"","hash":""},"upload":{"key":"upload","title":"","description":"","actor":"supplier","date":"","hash":""},"review":{"key":"review","title":"","description":"","actor":"client","date":"","hash":""},"cancel":{"key":"cancel","title":"","description":"","actor":["client","supplier"],"date":"","hash":""}},"states":{":initial":{"title":"","description":"","instructions":{},"actions":["request_quotation"],"default_action":"","transitions":[{"action":"request_quotation","response":"","condition":false,"transition":"invite_supplier"},{"action":"enter_client","response":"","condition":false,"transition":"provide_quote"}],"timeout":""},"invite_supplier":{"title":"Waiting on the supplier to participate in this process","description":"","instructions":{},"actions":["invite_supplier","cancel"],"default_action":"invite_supplier","transitions":[{"action":"invite_supplier","response":"ok","condition":false,"transition":"wait_for_quote"}],"timeout":""},"provide_quote":{"title":"Prepare quotation","description":"","instructions":{},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","condition":false,"transition":"invite_client"}],"timeout":""},"invite_client":{"title":"Waiting on the client to participate in this process","description":"","instructions":{},"actions":["invite_client","cancel"],"default_action":"invite_client","transitions":[{"action":"invite_client","response":"ok","condition":false,"transition":"wait_for_review"}],"timeout":""},"wait_for_quote":{"title":"Prepare quotation","description":"","instructions":{"supplier":{"<tpl>":" ( urgency)"}},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","condition":false,"transition":"wait_for_review"}],"timeout":{"<switch>":{"on":{"<ref>":"assets.request.urgency"},"options":{"normal":"3b","high":"1b","critical":"6h"}}}},"wait_for_review":{"title":"Review quotation","description":"","instructions":{"client":"Please review and accept the quotation","supplier":"Please wait for the client to review the quotation."},"actions":["review","cancel"],"default_action":"review","transitions":[{"action":"review","response":"accept","condition":false,"transition":":success"},{"action":"review","response":"deny","condition":false,"transition":":failed"}],"timeout":"7d"}},"definitions":{"request_form":{"title":"Quotation request form","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Supplier","name":"supplier","url":"https://jsonplaceholder.typicode.com/users","optionText":"name","optionValue":"{ name, email, phone }","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#textarea","label":"Description","name":"description","helptip":"Which service would you like a quotation for?"},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Urgency","name":"urgency","options":[{"value":"normal","label":"Normal"},{"value":"high","label":"High"},{"value":"critical","label":"Critical"}]}]}]},"client_form":{"title":"Enter client information","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#text","label":"Name","name":"name","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#email","label":"E-mail","name":"email","required":true}]}]}},"identity":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"$schema":"http:\/\/specs.livecontracts.io\/draft-01\/02-identity\/schema.json#","timestamp":"2018-03-30T16:01:40+0000","info":null},"timestamp":"2018-03-30T16:01:41+0000"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Response
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/responses/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $json = '{"$schema":"http:\/\/specs.livecontracts.io\/draft-01\/12-response\/schema.json#","process":{"id":"lt:\/processes\/111837c9-ff00-48e3-8c2d-63454a9dc234"},"actor":{"key":"client","id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","schema":"http:\/\/specs.livecontracts.io\/draft-01\/02-identity\/schema.json#","info":null},"timestamp":"2018-03-30T16:01:41+0000","action":{"key":"request_quotation"},"display":"always","data":{"description":"asd","urgency":"high"}}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Process done
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/processes/111837c9-ff00-48e3-8c2d-63454a9dc234/done', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $json = '{"id": "CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f", "lastHash": "8xJMEqxj1yUqqvHZwhfec8VPvSs1XRXWFQq1ZafEFAsR"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);

$I->seeNumHttpRequestWare(3);

$I->seeInCollection('event_chains', [
    "_id" => "CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f"
]);

$dbRecord = $I->grabFromCollection('event_chains', [
    "_id" => "CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f"
]);

foreach ($data['events'] as &$event) {
    $event['receipt'] = null;
}
$I->assertMongoDocumentEquals($data['events'], $dbRecord->events);

$resources = [
    "lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee",
    "lt:/processes/111837c9-ff00-48e3-8c2d-63454a9dc234"
];
$I->assertMongoDocumentEquals($resources, $dbRecord->resources);
