<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain');

$I->amSignatureAuthenticated("PIw+8VW129YY/6tRfThI3ZA0VygH4cYWxIayUZbdA3I9CKUdmqttvVZvOXN5BX2Z9jfO3f1vD1/R2jxwd3BHBw==");

$data = [
    "id" => "CtBfprZ4zktW4mVhh1hhU76AvqEa3vtpc5vN6gkDX5W9f",
    "events" => [
        [
            "timestamp" => "2018-03-30T16:01:40+00:00",
            "body" => "2D5Gw6o78SjNqL2L3FPainPnNyeuHUwbck7v75u3KmWsUGbgGQuHya2Zu9sWbqJ8STuVrkWN1WwTVGE8hKuC6zXMJ2j2JJntQv3G8EYUZpHUeFYFML8RUS1drQw9ufMhF4K644NrLmpJ1ioiccUhewpspPWe8AhCJ2VYVaMUtmcjF95f9RMpWxgPsYX4Wn92rPHeEnM8oX9bFnZBhoh2v1HRDJwmgHnvhU6Lukc8DyCgHwXaR6rBCqwidDsQZJTGdn6LQNJTBVmEYuK1o7DK6Kysvx4nAuaQW5R21SaELtupLaSefnZuUC9LtsLELQDnzz9VgKXy8zUG7ZT6QtwbMEdXCzE5GFTFcMthcuN9PcdQWKY3VQP9o3ewQFJZ5JLWCLq17UYW7EYoT39CEZwttkX1vgDDdcKL4zrLFo5JduqfSVqQ72z4J8j8UQPdbQTNodDWhjKz1EBXa",
            "previous" => "7juAGSAfJJ2Th9SXGpm3u9XcLtMZzFaExbnCrnUAi1kn",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "2xTP9LFqN9dW5f8PYwB5V76UJ18LgMj89GMKpnFxfRYZSfNKLVpphTL4Z5DStxenVLHyCFxfiCtbf3zwYsFYPNqK",
            "hash" => "2QRjSoTjPt3gF4UqZJ3bGBXMj3kkfUaagTPjPCPLCUxM"
        ],
        [
            "timestamp" => "2018-03-30T16:01:41+00:00",
            "body" => "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",
            "previous" => "2QRjSoTjPt3gF4UqZJ3bGBXMj3kkfUaagTPjPCPLCUxM",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "5Evm8rTKpUt2c9WTxgSq4PMsiMrJZQUCdTfzsE5Ux7YXY4JgNvgqe85tV9igLTYQhGXPbTDHjn6C8rwcvefQpuG8",
            "hash" => "7vrFf8bqc3oKHkNaJbM1HD8XwcRDkzKz27iMVAAbHyT"
        ],
        [
            "timestamp" => "2018-03-30T16:01:41+00:00",
            "body" => "2SwokuWCLbjvuatPFWpzQgbpEYZLGpnjfj19eMtSibTaLfEcW3R3CHcNoJqKqZCr69oQ2dfL9k8qcDh6mnpf37atsBLGT4akpKf7HCr2exhGyNwpr6Vo2cXAmCHqPTAMdbRUFBTmefgxGD7MggUQdudc5TAVJGNjNTGuJuzEaZJV7XL5k5pdkSDdhTgK62suhWemZV8Rvf7qTVjnYhEQ3HWwAQoaHzZrZEZxVoWX9LzR61DaCEGjTux5GFf5qB1d6i7GygNaidLWxHXGcygLtyS8Dbqi6Qx6iekzAc18yosGemZBVscTsFEGYTAFVfm9HsdyFutxS6JMLQLAjdwYFeSXxwkGywumDq38hDpZP892oyzCuhvjkY6YwQKgLMXUYsF5fxYaznNcCghygbnApPE2EYjZ79ixLvpRvWHMBUR3Dx9XQr8RpWs2hmzdErMDD6wbbSAEXe9oemr2Atncg53jNKwbEBPvwk1g2XLHTWL",
            "previous" => "7vrFf8bqc3oKHkNaJbM1HD8XwcRDkzKz27iMVAAbHyT",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "2sYWtWocxXXt5TTd6ZDXK8j9DC7FxDXPfFfABQeV5ajJzx11iaU8VHGeFJ3vvomHwDB1SH33AyKg34vQ8NmXsFU9",
            "hash" => "G5GxE9toTsFWFyczPtmjJHrvjqLk3VFY9Qo6FaYEsQmb"
        ]
    ]
];

// Scenario
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    
    $json = '{"$schema":"http://specs.livecontracts.io/draft-01/04-scenario/schema.json#","id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=6kE9aMwU","name":"","description":"Accept or reject a quotation","keywords":[],"tags":[],"info":"","assets":{"request":{"type":"object","properties":{"description":{"type":"string"},"urgency":{"type":"string","enum":["normal","high","critical"]}}},"quotation":{"$ref":"http://specs.livecontracts.io/draft-01/10-document/schema.json#"}},"actors":{"supplier":{"key":"","title":"","description":""},"client":{"key":"","title":"","description":""}},"actions":{"request_quotation":{"key":"","title":"","description":"","actor":"client","response":{},"date":"","hash":"","form":{"<ref>":"definitions.request_form"}},"invite_supplier":{"key":"","title":"","description":"","actor":"client","response":{},"date":"","hash":""},"enter_client":{"key":"","title":"","description":"","actor":"supplier","response":{},"date":"","hash":"","form":{"<ref>":"definitions.request_form"}},"invite_client":{"key":"","title":"","description":"","actor":"supplier","response":{},"date":"","hash":""},"upload":{"key":"","title":"","description":"","actor":"supplier","response":{},"date":"","hash":""},"review":{"key":"","title":"","description":"","actor":"client","response":{},"date":"","hash":""},"cancel":{"key":"","title":"","description":"","actor":["client","supplier"],"response":{},"date":"","hash":""}},"states":{":initial":{"title":"","description":"","instructions":{},"actions":["request_quotation"],"default_action":"","transitions":[{"action":"request_quotation","response":"","condition":false,"transition":"invite_supplier"},{"action":"enter_client","response":"","condition":false,"transition":"provide_quote"}],"timeout":""},"invite_supplier":{"title":"Waiting on the supplier to participate in this process","description":"","instructions":{},"actions":["invite_supplier","cancel"],"default_action":"invite_supplier","transitions":[{"action":"invite_supplier","response":"ok","condition":false,"transition":"wait_for_quote"}],"timeout":""},"provide_quote":{"title":"Prepare quotation","description":"","instructions":{},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","condition":false,"transition":"invite_client"}],"timeout":""},"invite_client":{"title":"Waiting on the client to participate in this process","description":"","instructions":{},"actions":["invite_client","cancel"],"default_action":"invite_client","transitions":[{"action":"invite_client","response":"ok","condition":false,"transition":"wait_for_review"}],"timeout":""},"wait_for_quote":{"title":"Prepare quotation","description":"","instructions":{"supplier":{"<tpl>":" ( urgency)"}},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","condition":false,"transition":"wait_for_review"}],"timeout":{"<switch>":{"on":{"<ref>":"assets.request.urgency"},"options":{"normal":"3b","high":"1b","critical":"6h"}}}},"wait_for_review":{"title":"Review quotation","description":"","instructions":{"client":"Please review and accept the quotation","supplier":"Please wait for the client to review the quotation."},"actions":["review","cancel"],"default_action":"review","transitions":[{"action":"review","response":"accept","condition":false,"transition":":success"},{"action":"review","response":"deny","condition":false,"transition":":failed"}],"timeout":"7d"}},"definitions":{"request_form":{"title":"Quotation request form","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Supplier","name":"supplier","url":"https://jsonplaceholder.typicode.com/users","optionText":"name","optionValue":"{ name, email, phone }","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#textarea","label":"Description","name":"description","helptip":"Which service would you like a quotation for?"},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Urgency","name":"urgency","options":[{"value":"normal","label":"Normal"},{"value":"high","label":"High"},{"value":"critical","label":"Critical"}]}]}]},"client_form":{"title":"Enter client information","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#text","label":"Name","name":"name","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#email","label":"E-mail","name":"email","required":true}]}]}},"identity":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"$schema":"http:\/\/specs.livecontracts.io\/draft-01\/02-identity\/schema.json#","timestamp":"2018-03-30T16:01:40+0000","info":null},"timestamp":"2018-03-30T16:01:41+0000"}';
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

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);

$I->seeNumHttpRequestWare(2);

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
