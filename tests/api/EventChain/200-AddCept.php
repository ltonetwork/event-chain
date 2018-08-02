<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$data = [
    "id" => "2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U",
    "events" => [
        [
            "timestamp" => 1522425700,
            "body" => "Bi6wFR7puBjdsvp1o9zCoyqFCJbczAuZBLfoB3M3t5WERbYEfgxrMKFFRC9EZZ7JDmrFLYE94uJAuzbdrcoumsUoPyEG4Vh6Hi8g7kK6B4soLMhDSqjgUhnRMfyFeKDLCU3Heve42TU6sRvrLpeGWs5G3A7Gzs44W2yzk6bz1WibVjKTJ1CABze5J1cMGFoKHNpAf2Z1XworrKDKf73FpkcMbbrrYd4i9Wg8oYoeV6uu1EaUit4XVCe8a97Ro9zSsbXnz1wLzwG2E7hkX2XGodDvAQHBa4vFqM2WJ4HpzDc4GSF79hXhMnGky8PXt4qJqEY28vDtjWr99vubu2aLEEDaX7WqrPDZuc1GL3HWKB35Dy9T112k527dci92V7nQ3yhWDhxwCtQW2VmCDx1eSmLsG6uXX2Mz4PgahwgL7C7Bayie1iGg2MXUXfcThEbu8ayThTA",
            "previous" => "J1qbMXtKt2ESZzJaN563LQJFGpwbkoCnNtyhyDP34Bhw",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "49KC1KfBecyUnwQQ6hL9XYmxj9tqfvtp2MzSo92eKTGVNQsFDayxkZr85fc77cvDuZZywdbeNHkeHAJr888PuETr",
            "hash" => "2TkZEjZV7fujY5uqsTk5T49V4rWgyWjRAcdv2pAZ5YDv"
        ],
        [
            "timestamp" => 1522425701,
            "body" => "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",
            "previous" => "2TkZEjZV7fujY5uqsTk5T49V4rWgyWjRAcdv2pAZ5YDv",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "4KNSk4dV8y4gyjxCy955Y4CEWcw4F3rueP9cGrNZNzBWd55nG9CjrnkGBKUL9rWefZKKy6cQQgCVFeF1SDZ2RQx4",
            "hash" => "Hj1fz65Mn7mtYieT8MTvAxKDmCLLB4RLj4SUFXc2Qybh"
        ],
        [
            "timestamp" => 1522425701,
            "body" => "5MbXSnuPL5E6aLV52TgRbikbE18GzwN7abSDA1GCsQpcMHRobmjQTXX2qpTpatRhLJvgg9PcDRLzS47aV7Pa1RRCRpjKadyTmmL12T6NpmBoNbdBGRur6DsPnHyskgJp2TzeZVTMD5qDcEay8HLrE3947aMJuWPYAzRNk3g8r1jiSNiuaVvjQkXyjAdNk2wJcmmJiAnCfr1zWWbaDrs923WF75B8WXwrknie3Rsb7x3T3vZq9MMARXSioP2FvadhFJffeFsJdGmGhRE2QhmYC1G3cjK7AzLUV9Ynp7u5Pk9VSvrGxmHTiwDcexk8HGCUChiXLirsHP2uR6qbUEH1HNhW87scLxvcVM9RkfVmD7jPhM3SVwZzoxzr8nGYjJVoNofs8iHFSbSqY1U5ic9Jbg5hHQJEcP8p39HUNXxgHTnwrLn3AvoHWFNuXpJNnZeiJTGRupCgX9Mpc27Q1MiSM4HAuLdyaQ6eUga9ELWwtVHugaVinWDQsXTs3EZQAjCxXPYmh4uixNrpkZS6BnZt45nzjoy9vtCsEho7L4qRkUbvg9jGpPDADD43HpwBrV7GwfMZ1RBSDxxBJkudwWf6Bz5B9RKaPxrHD3nPpFR3VMyCGo77ebqcKgakeLdKC9MVRdLxXWtQw4giLZcHe1LobGbhmfZ9XvK2Un4NVDDKohcUaojbSPpoW7mTwzAhksS1vasQXzANo5Ri62rDuAW9L8C6F4YkjBJm9R36M9ZDuKnVMMeDUYsCRZkdvyGG4sVf5stWoHcWX3juBLWMrwJv4AgyNAFivh5exHmfRWtrguxbEkRFi7N4h5d8uUMqLCofBry19N4UtVr77Dvinqdave2kLUVCvPcpAXESaTLTW23hQyYu9GBT9EAAHiWb68Vj4jJ7rpwXEoAW2cYkHgh88y8uJLgak4p6Xpun6WA5b49UUckUZMgsdubb4whxeKuHgwdW5tRXse1qgUmj12Cgt",
            "previous" => "Hj1fz65Mn7mtYieT8MTvAxKDmCLLB4RLj4SUFXc2Qybh",
            "signkey" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y",
            "signature" => "34UbRf5LjAj7wm5nbRJHyXM2U4bkyCq8YtVWDdYKZ5aL9f7dqwVrLRUuEM51p29H5P5TfR6JSwChncViiY6xsj7b",
            "hash" => "5AxcTcBRQAmYdoXdhsZtBJ9PwgAoheqs3KkBopTkUagM"
        ]
    ]
];

// Scenario
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"$schema":"https://specs.livecontracts.io/v0.1.0/scenario/schema.json#","id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=ATM7p9Wa","name":"Accept or reject a quotation","description":"Accept or reject a quotation","keywords":[],"tags":[],"info":{},"assets":{"request":{"type":"object","properties":{"description":{"type":"string"},"urgency":{"type":"string","enum":["normal","high","critical"]}}},"quotation":{"$ref":"https://specs.livecontracts.io/v0.1.0/document/schema.json#"}},"actors":{"supplier":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","key":"supplier","title":null,"description":null},"client":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","key":"client","title":null,"description":null}},"actions":{"request_quotation":{"key":"request_quotation","title":null,"description":null,"actor":"client","date":null,"hash":null,"form":{"<ref>":"definitions.request_form"},"responses":{"ok":{}}},"invite_supplier":{"key":"invite_supplier","title":null,"description":null,"actor":"client","date":null,"hash":null,"responses":{"ok":{}}},"enter_client":{"key":"enter_client","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"form":{"<ref>":"definitions.request_form"},"responses":{"ok":{}}},"invite_client":{"key":"invite_client","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"responses":{"ok":{}}},"upload":{"key":"upload","title":null,"description":null,"actor":"supplier","date":null,"hash":null,"responses":{"ok":{}}},"review":{"key":"review","title":null,"description":null,"actor":"client","date":null,"hash":null,"responses":{"accept":{},"deny":{}}},"cancel":{"key":"cancel","title":null,"description":null,"actor":["client","supplier"],"date":null,"hash":null,"responses":{"ok":{}}}},"states":{":initial":{"title":null,"description":null,"instructions":{},"actions":["request_quotation"],"default_action":null,"transitions":[{"action":"request_quotation","response":"ok","transition":"invite_supplier"},{"action":"enter_client","response":"ok","transition":"provide_quote"}],"timeout":null},"invite_supplier":{"title":"Waiting on the supplier to participate in this process","description":null,"instructions":{},"actions":["invite_supplier","cancel"],"default_action":"invite_supplier","transitions":[{"action":"invite_supplier","response":"ok","transition":"wait_for_quote"}],"timeout":null},"provide_quote":{"title":"Prepare quotation","description":null,"instructions":{},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","transition":"invite_client"}],"timeout":null},"invite_client":{"title":"Waiting on the client to participate in this process","description":null,"instructions":{},"actions":["invite_client","cancel"],"default_action":"invite_client","transitions":[{"action":"invite_client","response":"ok","transition":"wait_for_review"}],"timeout":null},"wait_for_quote":{"title":"Prepare quotation","description":null,"instructions":{"supplier":{"<tpl>":" ( urgency)"}},"actions":["upload","cancel"],"default_action":"upload","transitions":[{"action":"upload","response":"ok","transition":"wait_for_review"}],"timeout":{"<switch>":{"on":{"<ref>":"assets.request.urgency"},"options":{"normal":"3b","high":"1b","critical":"6h"}}}},"wait_for_review":{"title":"Review quotation","description":null,"instructions":{"client":"Please review and accept the quotation","supplier":"Please wait for the client to review the quotation."},"actions":["review","cancel"],"default_action":"review","transitions":[{"action":"review","response":"accept","transition":":success"},{"action":"review","response":"deny","transition":":failed"}],"timeout":"7d"}},"definitions":{"request_form":{"title":"Quotation request form","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/v0.1.0/form/schema.json#select","label":"Supplier","name":"supplier","url":"https://jsonplaceholder.typicode.com/users","optionText":"name","optionValue":"{ name, email, phone }","required":true},{"$schema":"http://specs.legalthings.one/v0.1.0/form/schema.json#textarea","label":"Description","name":"description","helptip":"Which service would you like a quotation for?"},{"$schema":"http://specs.legalthings.one/v0.1.0/form/schema.json#select","label":"Urgency","name":"urgency","options":[{"value":"normal","label":"Normal"},{"value":"high","label":"High"},{"value":"critical","label":"Critical"}]}]}]},"client_form":{"title":"Enter client information","definition":[{"fields":[{"$schema":"http://specs.legalthings.one/v0.1.0/form/schema.json#text","label":"Name","name":"name","required":true},{"$schema":"http://specs.legalthings.one/v0.1.0/form/schema.json#email","label":"E-mail","name":"email","required":true}]}]}},"identity":{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"$schema":"https://specs.livecontracts.io/v0.1.0/identity/schema.json#","timestamp":"2018-03-30T16:01:40+0000","info":null},"timestamp":"2018-03-30T16:01:41+0000"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Response
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/responses/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"$schema":"https://specs.livecontracts.io/v0.1.0/response/schema.json#","key":"ok","process":{"id":"lt:/processes/111837c9-ff00-48e3-8c2d-63454a9dc234","scenario":{"id":"lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee?v=H9eAveK7"}},"actor":{"key":"client","id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","name":"John Doe","email":"john.doe@example.com","node":"","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","schema":"https://specs.livecontracts.io/v0.1.0/identity/schema.json#","info":null},"timestamp":"2018-03-30T16:01:41+0000","action":{"key":"request_quotation"},"display":"always","data":{"description":"asd","urgency":"high"}}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Process done
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://legalflow/processes/111837c9-ff00-48e3-8c2d-63454a9dc234/done', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));

    $json = '{"id": "2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U", "lastHash": "5AxcTcBRQAmYdoXdhsZtBJ9PwgAoheqs3KkBopTkUagM"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Dispatch
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://event-dispatcher/queue?to%5B0%5D=', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"id":"2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U","events":[{"body":"5MbXSnuPL5E6aLV52TgRbikbE18GzwN7abSDA1GCsQpcMHRobmjQTXX2qpTpatRhLJvgg9PcDRLzS47aV7Pa1RRCRpjKadyTmmL12T6NpmBoNbdBGRur6DsPnHyskgJp2TzeZVTMD5qDcEay8HLrE3947aMJuWPYAzRNk3g8r1jiSNiuaVvjQkXyjAdNk2wJcmmJiAnCfr1zWWbaDrs923WF75B8WXwrknie3Rsb7x3T3vZq9MMARXSioP2FvadhFJffeFsJdGmGhRE2QhmYC1G3cjK7AzLUV9Ynp7u5Pk9VSvrGxmHTiwDcexk8HGCUChiXLirsHP2uR6qbUEH1HNhW87scLxvcVM9RkfVmD7jPhM3SVwZzoxzr8nGYjJVoNofs8iHFSbSqY1U5ic9Jbg5hHQJEcP8p39HUNXxgHTnwrLn3AvoHWFNuXpJNnZeiJTGRupCgX9Mpc27Q1MiSM4HAuLdyaQ6eUga9ELWwtVHugaVinWDQsXTs3EZQAjCxXPYmh4uixNrpkZS6BnZt45nzjoy9vtCsEho7L4qRkUbvg9jGpPDADD43HpwBrV7GwfMZ1RBSDxxBJkudwWf6Bz5B9RKaPxrHD3nPpFR3VMyCGo77ebqcKgakeLdKC9MVRdLxXWtQw4giLZcHe1LobGbhmfZ9XvK2Un4NVDDKohcUaojbSPpoW7mTwzAhksS1vasQXzANo5Ri62rDuAW9L8C6F4YkjBJm9R36M9ZDuKnVMMeDUYsCRZkdvyGG4sVf5stWoHcWX3juBLWMrwJv4AgyNAFivh5exHmfRWtrguxbEkRFi7N4h5d8uUMqLCofBry19N4UtVr77Dvinqdave2kLUVCvPcpAXESaTLTW23hQyYu9GBT9EAAHiWb68Vj4jJ7rpwXEoAW2cYkHgh88y8uJLgak4p6Xpun6WA5b49UUckUZMgsdubb4whxeKuHgwdW5tRXse1qgUmj12Cgt","timestamp":1522425701,"previous":"Hj1fz65Mn7mtYieT8MTvAxKDmCLLB4RLj4SUFXc2Qybh","signkey":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y","signature":"34UbRf5LjAj7wm5nbRJHyXM2U4bkyCq8YtVWDdYKZ5aL9f7dqwVrLRUuEM51p29H5P5TfR6JSwChncViiY6xsj7b","hash":"5AxcTcBRQAmYdoXdhsZtBJ9PwgAoheqs3KkBopTkUagM","receipt":null}],"identities":[{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","info":null,"node":"","name":"John Doe","email":"john.doe@example.com","signkeys":{"user":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"timestamp":"2018-03-30T16:01:40+0000","$schema":"https:\/\/specs.livecontracts.io\/v0.1.0\/identity\/schema.json#"}],"comments":[],"resources":["lt:\/scenarios\/fe659ffa-537d-461a-abd7-aa0f3643d5ee","lt:\/processes\/111837c9-ff00-48e3-8c2d-63454a9dc234"]}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(204);
});

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->dontSee("broken chain");
$I->seeResponseCodeIs(200);

$I->seeInCollection('event_chains', [
    "_id" => "2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U"
]);

$dbRecord = $I->grabFromCollection('event_chains', [
    "_id" => "2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U"
]);

foreach ($data['events'] as &$event) {
    $event['receipt'] = null;
}

$identities = [
    [
        "schema" => "https://specs.livecontracts.io/v0.1.0/identity/schema.json#",
        "id" => "0c1d7eac-18ec-496a-8713-8e6e5f098686",
        "signkeys" => [
            "user" => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"
        ],
        "name" => "John Doe",
        "email" => "john.doe@example.com",
        "privileges" => null,
        "encryptkey" => "BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6",
        "node" => '',
        'timestamp' => '2018-03-30T16:01:40+00:00',
        "info" => null
    ]
];
$I->assertMongoDocumentEquals($identities, $dbRecord->identities);

$resources = [
    "lt:/scenarios/fe659ffa-537d-461a-abd7-aa0f3643d5ee",
    "lt:/processes/111837c9-ff00-48e3-8c2d-63454a9dc234"
];
$I->assertMongoDocumentEquals($resources, $dbRecord->resources);

