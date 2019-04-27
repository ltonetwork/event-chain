<?php

/**
 * Try adding wrongly positioned events to existing chain
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$I = new ApiTester($scenario);
$I->wantTo('Try adding wrongly positioned events to existing chain');

$I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");

$chainId = '2arvKCGdNNAAJmxbHAHvCJs2zaBdwVktTnDwq8AUcFNAcYVeryk8awfeQJqdtD'; // existing chain
$data = [
    'id' => $chainId,
    'events' => [
        [
            'origin' => 'localhost',
            'body' => 'UVctBS7MyqiDkKXAsNngzz5HhXzWJB8o1ivmTF9FeogvNsDXMg4PpvtX8Wi8CCzxB4KKYa2qhiuEBfsXBGinJj6igSxAd3KGQbPVCJXMDHBJorZ8G8hHJs2tnKHxh9Ygh8pWH2UGmgLRnDekCNNsg4C8mV2bBNEcqrr83wgrqSmyEapXgdq3kPDLcLzVn3Re4R5WyNGZ2XZ7Zc8558zjGcWAJeCnbtXB17xHJJBFsWbqrzKSUwNcKA1E6xrhq24BJr2CySBucZxbho95aSyzvNJWVkYhAAXtrWGxFwrq3B9tvfb4pW8uSZLXby7k9VLPKvyZi2yBeVJznyh8ArWhzyodvLWbRh1kAPVGkKN2CXgFkp',
            'timestamp' => 1553814481,
            'previous' => 'J4pM5KNkrzeBb8233uFCq1tVRGN4LQ3SVNyDGU3Ys2Jw', // last event of existing chain
            'signkey' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'signature' => '3ajwcF4NjRpPAgGHrezzDVf2LRCGjYdqTUHKCZ26Q8qW7RQAEm4TS39Dt3VpqkVbMuxAbMsz2Ywu3zqou8yZqspV',
            'hash' => 'HfUCz3Bhoqkv83MKZU7CdDBp6kA8h1CvJzhxRMkavNXC',
            'receipt' => NULL,
            'original' => NULL,
        ],
        [ // this event should be third
            'origin' => 'localhost',
            'body' => 'UVctBS7MyqiDkKXAsNngzz5HhXzWJB8o1ivmTF9FeogvNsDXMg4PpvtX8Wi8CCzxB4KKYa2qhiuEBfsXBGinJj6igSxAd3KGQbPVCJXMDHBJorZ8G8hHJs4zodszASjfsqYN7rH9kxpuyiqAUFhDqeozRNaWuJ5mgeND2MeF6E6SJ2QPE58A9QHn11QdJCJ2mNoA1p5GPRVWX8DF2bQa2YqrmHM9ZQBXLSAr888zuo9j15zE7ijmnKqWkuTgyEvpyCAP3eToiW1CFcYip2DzkiQWxwFDjkSCFqWsB93ocF3BzpLXbHGzhewVu2cCuzgzceKJ79e5p7bVi1D7186DmdvJ1grmXkrXXSkoStYtVm7yd6',
            'timestamp' => 1553814481,
            'previous' => 'F3jCiAUmNkttn2uWDAXk7Qn8ebLA4rvEah4gYWvp85mC',
            'signkey' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'signature' => '4ZoZ926hNQs3ZJbyNFXfcfV9GFuexkjnrJhUZUgm3bWPpWCmxLZAeGBg2C2oiuFRZGqSi3WtJmBxPJNJbhXC2bWS',
            'hash' => '3Cjuf3F3DaKzoQW32HdH5JfVgv2ciGdNvhQHe2yjRXDp',
            'receipt' => NULL,
            'original' => NULL,
        ],
        [ // this event should be second
            'origin' => 'localhost',
            'body' => 'UVctBS7MyqiDkKXAsNngzz5HhXzWJB8o1ivmTF9FeogvNsDXMg4PpvtX8Wi8CCzxB4KKYa2qhiuEBfsXBGinJj6igSxAd3KGQbPVCJXMDHBJorZ8G8hHMapi2oKHzBbARAgRQTzXRgfbNBzsxyzaigWNKQNaiKHcE1ALR38G57sm29hQKqYHvvvJ5wtiknSnr87BzETmEYT1EcdAmdWL2FD3hMDAQyCdrVTJSskQSxa4mVRVoBL7jFsPAVMSV2mdzUF69WKH3EvLRhSyZkcmqCxcNF6ZTzb5JTFUVmjSaBKU3bLSMzoH2Eseq62PXsB9F8ngHLEfjnRJDYaekGF8sDGmgycfTMihCYgVdRGL3JwgcL',
            'timestamp' => 1553814481,
            'previous' => 'HfUCz3Bhoqkv83MKZU7CdDBp6kA8h1CvJzhxRMkavNXC',
            'signkey' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y',
            'signature' => '27fPdkEuksyRCDUojnUrN8UJeYUhbs3PEyEtAL7FWs4hickZ2Ap7LHCRvXP82XtDrPeeZzxxSav6hXUYKqAcyVem',
            'hash' => 'F3jCiAUmNkttn2uWDAXk7Qn8ebLA4rvEah4gYWvp85mC',
            'receipt' => NULL,
            'original' => NULL,
        ]
    ]
];

$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('/event-chains', $data);

$I->expectTo('see error message');

$I->seeResponseCodeIs(400);
$I->seeResponseIsJson();
$I->seeResponseContains("broken chain; previous of '3Cjuf3F3DaKzoQW32HdH5JfVgv2ciGdNvhQHe2yjRXDp' is 'F3jCiAUmNkttn2uWDAXk7Qn8ebLA4rvEah4gYWvp85mC', expected 'HfUCz3Bhoqkv83MKZU7CdDBp6kA8h1CvJzhxRMkavNXC'");

$I->expectTo('see that chain did not change');

$I->sendGET('/event-chains/' . $chainId);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseIsEventChain($chainId, ['latest_hash']);
