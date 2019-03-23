<?php

// FIXME: events are not being anchored. Bug in test or in code?

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Add a new event chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw=="); // wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp

$scenario = file_get_contents('tests/_data/scenarios/basic-user-and-system.json');
$scenario = json_decode($scenario, true);

$bodies = [
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
        'id' => '0c1d7eac-18ec-496a-8713-8e6e5f098686',
        'node' => 'localhost',
        'signkeys' => [
            'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
            'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ],
        'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
    ],
    $scenario,
    [
        '$schema' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#',
        'id' => 'j2134901218ja908323434',
        'scenario' => '2557288f-108e-4398-8d2d-7914ffd93150'
    ]
];

$chain = $I->createEventChain(3, $bodies);
$data = $I->castChainToData($chain);

// AnchorClient
/*
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "DGz1cLwsckf5k5EDMHZhAsf5HwcuEr5WdqWpJ8jSsZKa", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());

    return new Response(200);
});*/

// Save identity to workflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[0];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][0]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/identities/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// AnchorClient
/*
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "9iLXBD2JYgxv3qgqeUM7NFBJr7KKjjxSsSv9oBaTntBp", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});*/

// Create scenario at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[1];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][1]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/scenarios/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// AnchorClient
/*
$I->expectHttpRequest(function (Request $request) use ($I) {
    $I->assertEquals('http://anchor/hash', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $json = '{"hash": "C8jeCtm6m3mHXJpWbcQLRCaW3N3SkvfmUMcbns47g57H", "encoding": "base58"}';
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
}); */

// Start process at legalflow
$I->expectHttpRequest(function (Request $request) use ($I, $bodies, $data) {
    $body = $bodies[2];
    $body['timestamp'] = $I->getTimeFromEvent($data['events'][2]);    
    $json = json_encode($body);

    $I->assertEquals('http://legalflow/processes/', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Send message to process
$I->expectHttpRequest(function (Request $request) use ($I) {
    $json = json_encode(['id' => 'j2134901218ja908323434']);
    
    $I->assertEquals('http://legalflow/processes/j2134901218ja908323434/invoke', (string)$request->getUri());
    $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
    $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
    return new Response(200);
});

// Dispatch new nodes
// $I->expectHttpRequest(function (Request $request) use ($I) {
//     $I->assertEquals('http://event-queuer/queue?to%5B0%5D=node1', (string)$request->getUri());
//     $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
//     $json = '{"id":"2bPEu5vurhhAaTuNRYwGi3S8VovHUir31cg4JSUuP22dcgL3iYAZC1Zb5BmE5U","events":[{"origin":"node1","body":"6LHsKnaW3ra3NJxs2vWfqRS6BUGxiAoFbjT1NkQygcxChd1m7RYvgipUZ54s2osaMT3TLc54Qp48F5Zwxg2dn8z9cEbPKSFy2KEUyVu2imYM8snvKcvuRgR92KqtF3dZBJUiTLAwunyzFGfhEAQtzHZF32RwWB5edBKngGdnoqsmUsWDoxYvxb49hLmNAmFLbmtWzbTFtfP3dan5hrjEVaxXv3CNT8hwsFgN3pbaEZX2YH2mK9X4dtSEVS3vPiTKCJz26UQXaiLRBQAzivWq2QZpSmNTcC88h8Lg82TPbMoxfvLA4kwcQeZhbjeK4NKc39DZe3Mzbb5NLNeAUUd74Ty7Q9nwVYpbFwXw7hRLUsy4nFqkEUyrsyHwVMyRtj2Vfc4t9saWmwdHG2SkyKcvQAbRvTd2nC3u5dKtFbb3VXQASu2RQXxdL9uaZaa8BP2dieRzVHarSFo2T6","timestamp":1522425700,"previous":"J1qbMXtKt2ESZzJaN563LQJFGpwbkoCnNtyhyDP34Bhw","signkey":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y","signature":"3Vj7CiF53mdTsfZJLJeBFfD4cmrYe1UxgTttdJebJKKTFKEP88ZZWq2zDHL3RJpEtWq6gFMKZc6pRb2E2LGsPa6L","hash":"DGz1cLwsckf5k5EDMHZhAsf5HwcuEr5WdqWpJ8jSsZKa","receipt":null,"original":null},{"origin":"node1","body":"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","timestamp":1522425701,"previous":"DGz1cLwsckf5k5EDMHZhAsf5HwcuEr5WdqWpJ8jSsZKa","signkey":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y","signature":"5U3zQUZhWKv8mzygKgsmqgxnSudTC3HbqhZKmjoNCnpGpKSAEYkkyqth7CLRh7SaYVZwt7afB9hNKDUrKCkAytNo","hash":"9iLXBD2JYgxv3qgqeUM7NFBJr7KKjjxSsSv9oBaTntBp","receipt":null,"original":null},{"origin":"node1","body":"3AnXwjiDsffucpMANXuLoEnBGinR2LUjg9ty8Uxo3hFWGe5x1oH5F7XiQjEyUFz4hDBLwngx5BEBFxr9Pa91KXp9cB13K5XNkZ3yXZMgDUpoJo2YdDXZgjHuPBozccpYcUj6tbjdn17cCPAZm7vNdkqy2XJZhgH9QMtoTzoqNeZFm7TzHKhzZPqwzLGRUkhYqrY4VG5VA1xWrDDiH2jsjVK2nKXeAGp85up7111CCyJvmwSDZ8GkqKJWLudEghaLiJnoyMB8VgSoUtJgMi1SLV4qhFWWsEJmaKdsQzbjDUKDYYc71fdLUPvJSsm148YRNyhEV1Ek6bPYxSVr1ATumxuyCWWeiZDLoKjKN1F46T3RLmwnkizFLdiNKrN5jCZX6xhy57dNBUn9rPUMV3cyJQNqJqfR1J4pn5y2m63Gd16YcePgLYJ7FC9jRDp5h4AFjYbn34vqSXjAEB8UH2vGNauqUW1n12XByDqZCNY8nttXq9ucHJsCS3gXoKdGHYg89h4yYNww89k8KxX5fbcPWT87fNozzyKwZhZNrhEg6N7dwdRi1dNCLEiTKofAyPfiDxN2Frcc82yefWR3EZqiHAzHSWxE9ahPSfnrZijosaqbz1RxGUZnvGUbtP2v6ta72L7izsnno1dJVUTUY5odKX2SRYrhHkoSdRJhMV7zkhFZtvwd6K4SejjdF5kZxo2dSAesfZReTBxuY6F4f3Nqm29wyJrHDPMskdZGTgz5pieKzE15K83KvVmZbzyCywy7nJZBdv24Vc8o11hq3afZtXtDZJaT5T44hL68T2NYWc7au3pqUjfbbCKemsbvHBuTtwJZJqb9XhAgnVu9pjkKf7pzcfACwgW1yN9gdVKW9wJnx2F5EjCQuXSZPD3E1ckribi9xbbDDMnDHGQkYZrejybYJTBJQvu4Srh8fjYNmvTj5i4uVBKd84M5ftWoNW8naghRBgx6LfHdwrbez6j6kXr6AHXS","timestamp":1522425701,"previous":"9iLXBD2JYgxv3qgqeUM7NFBJr7KKjjxSsSv9oBaTntBp","signkey":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y","signature":"2ucLSpSM7c2qn9bv7DwvRFm3db4ExJDT79NigGoPzCQ5JWx2NBxMurwGnacfAsVfY9kCkZ1j1fBNHoaw4PTuAoMf","hash":"C8jeCtm6m3mHXJpWbcQLRCaW3N3SkvfmUMcbns47g57H","receipt":null,"original":null}],"identities":[{"id":"0c1d7eac-18ec-496a-8713-8e6e5f098686","info":null,"node":"node1","name":"John Doe","email":"john.doe@example.com","signkeys":{"default":"FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"},"encryptkey":"BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6","privileges":null,"timestamp":"2018-03-30T16:01:40+0000","$schema":"https:\/\/specs.livecontracts.io\/v0.1.0\/identity\/schema.json#"}],"resources":["lt:\/scenarios\/fe659ffa-537d-461a-abd7-aa0f3643d5ee","lt:\/processes\/111837c9-ff00-48e3-8c2d-63454a9dc234"]}';
//     $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
    
//     return new Response(204);
// });

$I->haveHttpHeader('Content-Type', 'application/json');
$I->haveHttpHeader('Digest', $I->calculateDigest($data));
$I->sendPOST('/event-chains', $data);

$I->expectTo('see chain in response');

$I->dontSee('broken chain');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['id' => $chain->id]);

$I->expectTo('obtain saved chain');

$I->sendGET('/event-chains/' . $chain->id);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['id' => $chain->id]);
