<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$data = [
    "id" => "L1hGimV7Pp2CWTUViTuxakPRSq61ootWsh9FuLrU35Lay",
    "events" => [
        [
            "timestamp" => "1522425700",
            "body" => "2D5Gw6o78SjNqL2L3FPainPnNyeuHUwbck7v75u3KmWsUGbgGQuHya2Zu9sWbqJ8STuVrkWN1WwTVGE8hKuC6zXMJ2j2JJntQv3G8EYUZpHUeFYFML8RUS1drQw9ufMhF4K644NrLmpJ1ioiccUhewpspPWe8AhCJ2VYVaMUtmcjF95f9RMpWxgPsYX4Wn92rPHeEnM8oX9bFnZBhoh2v1HRDJwmgHnvhU6Lukc8DyCgHwXaR6rBCqwidDsQZJTGdn6LQNJTBVmEYuK1o7DK6Kysvx4nAuaQW5R21SaELtupLaSefnZuUC9LtsLELQDnzz9VgKXy8zUG7ZT6QtwbMEdXCzE5GFTFcMthcuN9PcdQWKY3VQP9o3ewQFJZ5JLWCLq17UYW7EYoT39CEZwttkX1vgDDdcKL4zrLFo5JduqfSVqQ72z4J8j8UQPdbQTNodDWhjKz1EBXa",
            "previous" => "EfhsVxGsRYH2cEDcivaqtb54WQiq71yE543ci95MYC2Z",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "5THqg39Wx6aNJqcmFUHk4MdgpBiFk25Lk2mD3gRKrVbwDx9w4oDiQgNR7NKkDguHohKdtwNkmMCsKxUsPkLFkRwx",
            "hash" => "1VrV8ysznt7YqvLkfe3JEfi3F3EuwtXrfzRT1qochNy"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "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",
            "previous" => "1VrV8ysznt7YqvLkfe3JEfi3F3EuwtXrfzRT1qochNy",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "5wU4xweyL8R8gGzSxGgwn1rjnajzswmZokfuxbKrfXKWXZRcJxk4GjkEpACKyLKop53bJ9cCEmjuGELGCzLPou3R",
            "hash" => "5BQWg6TRpmkuwjskLdJL31VwVGv7xPLKFpYvJ5NaJpe1"
        ],
        [
            "timestamp" => "1522425701",
            "body" => "3AnXwjiDsffucpMANXuLoEkLMkPGneXc9Bjihjwm7BmpwdaPLbTafTp3mvMF3FPaCvxHdDv937vhMyJF4stCzaokdYqV4a5xuDqgW3aVvDZgWY2jdZQs64hXKnQir1SnwsEkJiT1HSC13jcAYc5mGXSK629nj6KaECTxFFaeNhC2f7PiFyqDb9ReCBTYdpfEtoAb9paCzLGqrgMzi9goEh2L11CR6SPaZRo9PZ2XHJQc8wpuZgZ7cc1DJ5KUKsjNruSHn7Vh4wErg7GqyBhS29oy6rK6fW3iGtDX1oFwuiRk1P1XpiU8imUgWhAKimNzqnfND3wqoVrgk9egcZf9BGpYQRQgZdg8RcijxwzV7pgdaMWRoF8YL5dtwxs4pLCFDuYzopRzhZx9wihQMXMoNsudHbG8pNGghWoKyze184wHqmeydM9kogt9ynM5d61NH2vTov1ertGKkuh4Y1fxM8djfX85qYv2jgoQDqVdY5x7SYSgjp8tp6pDiNpsghZbkaNkss9563feTwP3UJpixQp1isVhK4G8zwqz8dBqxQ8T3oox9RCdKiYuYFGfDBSf8cnaH58a9iseCa8W6RJkcZEqzL5SSGGYRucVvhVvPEDTbGfK9pbM7YBudF1VuJ5YeF8M1aFrr9oP6wkqL8NJxZvGSWL4dAKwpxnAtSSvE6TC6QE5VeD4sWX4P29SaZGtXFstx85kTh8aDiUbYKZfPndACLjFMeA6veVNuhuArEz1c6S4Xs4c1yNYSvVhcYijDLVxfubntZf3xuAxG348VnhPneztFPy6ZhPmvtDuKXD3udgyAVK8LTSCfKjeuWGyMj2aYWMWmZ2H4r8k7NtkJBdg5dTgAQ718xm2rL39k8zHWtPZNEwRp1ipp9oMG8GqiBqHcEY4C1K6bWnQetF27CxxXSzgvN9BH4ECMfEqUUvgjYWMDea4dkFijHmRG32sfwFBKa1qGKibAS2P1bj5XBzcg5j2",
            "previous" => "5BQWg6TRpmkuwjskLdJL31VwVGv7xPLKFpYvJ5NaJpe1",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "K16CX4TfiFi5Rtzg4jKQCfQRTMmiYTr1LvdVCJCuzzej8iYdVr9kGfgsLjkQj6KZawdECEj4Z5ZdHUyn3VhwYYp",
            "hash" => "6z4v2sknAWRtZjZGzudhANxq2mzFJ9ijiDrmk12nWMr9"
        ]
    ]
];

// Scenario
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    
    $json = '{"$schema":"http://specs.livecontracts.io/draft-01/04-scenario/schema.json#","id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=71xNcsSx","name":"Accept or reject a quotation","description":"Accept or reject a quotation","keywords":[],"tags":[],"info":{},"assets":{"request":{"type":"object","properties":{"description":{"type":"string"},"urgency":{"type":"string","enum":["normal","high","critical"]}}},"quotation":{"$ref":"http://specs.livecontracts.io/draft-01/10-document/schema.json#"}},"actors":{"supplier":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","key":"supplier","title":null,"description":null},"client":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","key":"client","title":null,"description":null}},"actions":{"request_quotation":{"key":"request_quotation","title":null,"description":null,"actor":"client","date":null,"hash":null,"form":{"<ref>":"definitions.request_form"},"responses":{"ok":{}}},"invite_supplier":{"key":"invite_supplier","title":null,"description":null,"actor":"client","date":null,"hash":null,"responses":{"ok":{}}},"enter_client":{"key":"enter_client","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"form":{"<ref>":"definitions.request_form"},"responses":{"ok":{}}},"invite_client":{"key":"invite_client","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"responses":{"ok":{}}},"upload":{"key":"upload","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"responses":{"ok":{}}},"review":{"key":"review","title":null,"description":null,"actor":"client","date":null,"hash":null,"responses":{"accept":{},"deny":{}}},"cancel":{"key":"cancel","title":null,"description":null,"actor":["client","supplier"],"date":null,"hash":null,"responses":{"ok":{}}}},"states":{":initial":{"title":null,"description":null,"instructions":{},"actions":["request_quotation"],"default_action":null,"transitions":[{"action":"request_quotation","response":"ok","transition":"invite_supplier"},{"action":"enter_client","response":"ok","transition":"provide_quote"}],"timeout":null},"invite_supplier":{"title":"Waiting on the supplier to participate in this process","description":null,"instructions":{},"actions":["invite_supplier","cancel"],"default_action":"invite_supplier","transitions":[{"action":"invite_supplier","response":"ok","transition":"wait_for_quote"}],"timeout":null},"provide_quote":{"title":"Prepare quotation","description":null,"instructions":{},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","transition":"invite_client"}],"timeout":null},"invite_client":{"title":"Waiting on the client to participate in this process","description":null,"instructions":{},"actions":["invite_client","cancel"],"default_action":"invite_client","transitions":[{"action":"invite_client","response":"ok","transition":"wait_for_review"}],"timeout":null},"wait_for_quote":{"title":"Prepare quotation","description":null,"instructions":{"supplier":{"<tpl>":" ( urgency)"}},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","transition":"wait_for_review"}],"timeout":{"<switch>":{"on":{"<ref>":"assets.request.urgency"},"options":{"normal":"3b","high":"1b","critical":"6h"}}}},"wait_for_review":{"title":"Review quotation","description":null,"instructions":{"client":"Please review and accept the quotation","supplier":"Please wait for the client to review the quotation."},"actions":["review","cancel"],"default_action":"review","transitions":[{"action":"review","response":"accept","transition":":success"},{"action":"review","response":"deny","transition":":failed"}],"timeout":"7d"}},"definitions":{"request_form":{"title":"Quotation request form","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Supplier","name":"supplier","url":"https://jsonplaceholder.typicode.com/users","optionText":"name","optionValue":"{ name, email, phone }","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#textarea","label":"Description","name":"description","helptip":"Which service would you like a quotation for?"},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#select","label":"Urgency","name":"urgency","options":[{"value":"normal","label":"Normal"},{"value":"high","label":"High"},{"value":"critical","label":"Critical"}]}]}]},"client_form":{"title":"Enter client information","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#text","label":"Name","name":"name","required":true},{"$schema":"http://specs.legalthings.one/draft-01/08-form/schema.json#email","label":"E-mail","name":"email","required":true}]}]}},"identity":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":null,"signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"$schema":"http://specs.livecontracts.io/draft-01/02-identity/schema.json#","timestamp":"2018-03-30T16:01:40+0000","info":null},"timestamp":"2018-03-30T16:01:41+0000"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Response
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/responses/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $json = '{"$schema":"http://specs.livecontracts.io/draft-01/12-response/schema.json#","key":"ok","process":{"id":"lt:/processes/111837c9-ff00-48e3-8c2d-63454a9dc234","scenario":{"id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=H9eAveK7"}},"actor":{"key":"client","id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","schema":"http://specs.livecontracts.io/draft-01/02-identity/schema.json#","info":null},"timestamp":"2018-03-30T16:01:41+0000","action":{"key":"request_quotation"},"display":"always","data":{"description":"asd","urgency":"high"}}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Process done
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/processes/111837c9-ff00-48e3-8c2d-63454a9dc234/done', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $json = '{"id": "L1hGimV7Pp2CWTUViTuxakPRSq61ootWsh9FuLrU35Lay", "lastHash": "6z4v2sknAWRtZjZGzudhANxq2mzFJ9ijiDrmk12nWMr9"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);

$I->seeNumHttpRequestWare(3);

$I->seeInCollection('event_chains', [
    "_id" => "L1hGimV7Pp2CWTUViTuxakPRSq61ootWsh9FuLrU35Lay"
]);

$dbRecord = $I->grabFromCollection('event_chains', [
    "_id" => "L1hGimV7Pp2CWTUViTuxakPRSq61ootWsh9FuLrU35Lay"
]);

foreach ($data['events'] as &$event) {
    $event['receipt'] = null;
}
$I->assertMongoDocumentEquals($data['events'], $dbRecord->events);

$identities = [
    [
        "schema" => "http://specs.livecontracts.io/draft-01/02-identity/schema.json#",
        "id" => "0c1d7eac-18ec-496a-8713-8e6e5f098686",
        "signkeys" => [
            "user" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"
        ],
        "name" => "John Doe",
        "email" => "john.doe@example.com",
        "privileges" => null,
        "encryptkey" => "BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6",
        "node" => '',
        'timestamp' => '2018-03-30T16:01:40+00:00'
    ]
];
$I->assertMongoDocumentEquals($identities, $dbRecord->identities);

$resources = [
    "lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee",
    "lt:/processes/111837c9-ff00-48e3-8c2d-63454a9dc234"
];
$I->assertMongoDocumentEquals($resources, $dbRecord->resources);

