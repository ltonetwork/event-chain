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
            "signature" => "eXfU4NZciDj9sutGMKayugauHXafHmkdHWwrPcmbFjVRruQuM7xkKFV9TqT3TL6Ks4fndFy35vgWWjPZUtyEveD",
            "hash" => "3GkwLumJTJucCtnAm6Cc7vsFfhTc3FYPRMRW4rPgLp6f"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "3AnXwjiDsffucpMANXuLoEkLMkPGneXc9Bjihjwm7BmpwdaPLbTafTp3mvMF3FPaCvxHdDv937vhMyJF4stCzaokdYqV4a5xuDqgW3aVvDZgWY2jdZQs64hXKnQir1SnwsEkJiT1HSC13jcAYc5mGXSK629nj6KaECTxFFaeNhC2f7PiFyqDb9ReCBTYdpfEtoAb9paCzLGqrgMzi9goEh2L11CR6SPaZRo9PZ2XHJQc8wpuZgZ7cc1DJ5KUKsjNruSHn7Vh4wErg7GqyBhS29oy6rK6fW3iGtDX1oFwuiRk1P1XpiU8imUgWhAKimNzqnfND3wqoVrgk9egcZf9BGpYQRQgZdg8RcijxwzV7pgdaMWRoF8YL5dtwxs4pLCFDuYzopRzhZx9wihQMXMoNsudHbG8pNGghWoKyze184wHqmeydM9kogt9ynM5d61NH2vTov1ertGKkuh4Y1fxM8djfX85qYv2jgoQDqVdY5x7SYSgjp8tp6pDiNpsghZbkaNkss9563feTwP3UJpixQp1isVhK4G8zwqz8dBqxQ8T3oox9RCdKiYuYFGfDBSf8cnaH58a9iseCa8W6RJkcZEqzL5SSGGYRucVvhVvPEDTbGfK9pbM7YBudF1VuJ5YeF8M1aFrr9oP6wkqL8NJxZvGSWL4dAKwpxnAtSSvE6TC6QE5VeD4sWX4P29SaZGtXFstx85kTh8aDiUbYKZfPndACLjFMeA6veVNuhuArEz1c6S4Xs4c1yNYSvVhcYijDLVxfubntZf3xuAxG348VnhPneztFPy6ZhPmvtDuKXD3udgyAVK8LTSCfKjeuWGyMj2aYWMWmZ2H4r8k7NtkJBdg5dTgAQ718xm2rL39k8zHWtPZNEwRp1ipp9oMG8GqiBqHcEY4C1K6bWnQetF27CxxXSzgvN9BH4ECMfEqUUvgjYWMDea4dkFijHmRG32sfwFBKa1qGKibAS2P1bj5XBzcg5j2",
            "previous" => "3GkwLumJTJucCtnAm6Cc7vsFfhTc3FYPRMRW4rPgLp6f",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "4iBRN2jYuCCt5RBs7C3HNJjJqSwMnTT2Hd7xVUgTfDZpG7FrqbEZkNqxwRxoV3B6p6eoTqYemBH7JtvvRLfS1Di1",
            "hash" => "9p93831f3vPQFgC4kGiKLYviVStxL8ZxxzJgjvYufzf7"
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
