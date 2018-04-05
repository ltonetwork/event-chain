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
            "signature" => "2fTVNQzRSwU14JRmBeoPo9JkZpse5RCcUNRYfDw9dwjCgpkXq4zz4nvQwiGTjoJCVNzEDT66FAqWqg4doFgbybxE",
            "hash" => "FqunY5iygwV3xTKjqpwiJxvRer25SKQmWTAVVphyTuQX"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "3AnXwjiDsffucpMANXuLoEkLMkPGneXc9Bjihjwm7BmpwdaPLbTafTp3mvMF3FPaCvxHdDv937vhMyJF4stCzaokdYqV4a5xuDqgW3aVvDZgWY2jdZQs64hXKnQir1SnwsEkJiT1HSC13jcAYc5mGXSK629nj6KaECTxFFaeNhC2f7PiFyqDb9ReCBTYdpfEtoAb9paCzLGqrgMzi9goEh2L11CR6SPaZRo9PZ2XHJQc8wpuZgZ7cc1DJ5KUKsjNruSHn7Vh4wErg7GqyBhS29oy6rK6fW3iGtDX1oFwuiRk1P1XpiU8imUgVn3zRRikarin5urw8DUk3FwBVdZbaG47SpiXSPhRTUaHnGpCW2XrFkGtxfmgxQVfSnfPQgrqKRbv2PpZtFvTuhzj1MH3r1J5QTPwo5zUEtuVqRE6gypcet7Psn88Esu7fDFRytgQSypqgcBimQ1hSTcMiRJK9Qq5KE8whWkzcQwGpouD5veMuzQ3i5Cs3fonETY4ZQUzJac2vQ9iHJ4ceAofgzLLRszVg6U2z64r8azJs8nsyPKDSyMD2f4indDzbGZ3AjsH4JwamPoeJMqWLPXVirATkLvpLGtiMxxThVzkArkscPp5nxWCk2jRPBQDm6FFVGTud1c2qbXJ7X9uAYEHxiTFLk7RbKoWm9VBdspNemFbwmMBWmEScZt7sFX6PrMapkJ1u1fyXpNFyQzaqvYCTuMXvkfUELVhhC6Nnz62kmJKktQbCBP5sZo3tU92FVDc29L22sXTjoVMtNZdtNB1zw78JbHz2X5KGRcY289ya3vjggk1RstHeNWd95q8v1KGTDKsZr3gPRpS2d3LWtaEyqRTxdVu8iVTCsggtAE4HVMY6Su7f4KU1Mb9hpta3AT1CoY7TAyGzJrNaxyGj5DAcuSEStRyVhSEZsNxC6zFhUSRJDMPEabfxvRDYNTZ9VXtcygZ9ZkYtovfRnBAp5KTcSoG84hsF6vx",
            "previous" => "FqunY5iygwV3xTKjqpwiJxvRer25SKQmWTAVVphyTuQX",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "2WhDu5zfFz8bE3HHCrL9dYZvbGoDGDYLqUcrbYxcaTcugVKra8J3X3sHoM6BMTn3rtKrekTJuCKVAmu4SCaiw1Yh",
            "hash" => "GAeDs3LBXu6QXiQaGvniGJ8uDaXtR91K7o7GHD3xkwpn"
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
