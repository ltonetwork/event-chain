<?php

/**
 * Queue event chain, that has invalid id
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try to queue event chain, that has invalid id');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$data =[
    'id' => 'foo',
    'events' => [
        [
            'origin' => 'localhost',
            'body' => 'AEcg7ejVDJx6iW6qXBn1f9vxc3a1DobG8uuodMEiotY1YBKqXZgYRVDb2THJAJr1Dw8uh2nNZj2XeFyabT2ZvoDyWefszeVchubLimFd4gqLhEdDUFKsFwtYRiJGAsLji8CZz7CYENNGnSGMVyWhgkQVhZ7z2nbzrNSnayrFoBPqMVkxfX5nZE2mrCG3ogaoq8fXzxFvbX5pRs1F1scPtk6RThZD31m2wpkHhh1povnByE2d6iV5j2bmQR7DZcbLFZmrTuSw1LESmcwMfqg9TcpoSFkdxkht1Z7JXBnDqZG54XZKc7jASGZvqpNx63jKzvv561A41eEZhjF4QcWbEikwy5o4oEfEdxwu3aHsW7cd3fwGp',
            'timestamp' => 1553817203,
            'previous' => 'EA4qVR9TAyndvRRMhmxa4ydtm1qfeeLrDSr2ef4FjJuh',
            'signkey' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'signature' => '4ZYBFnPT49oXg7c5gvgrjJZZvTb6pFcZu5KjSdmE4FY3gxuzEvUedsaM7vWu38B3pTduwhu6y7kxmFq3TqnVnvja',
            'hash' => '738nFDTAkCKVjxwWjuyf57TVvUNKgf1kuDx6Jeg6pGEW',
            'receipt' => NULL,
            'original' => NULL,
        ],
    ],
];

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/queue', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains('invalid id');
