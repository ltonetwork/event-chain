<?php

/**
 * Add event chain, that has event with invalid body
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class AddInvalidEventBodyCest
{
    /**
     * Logout after each test
     */
    public function _before(ApiTester $I): void
    {
        $I->amSignatureAuthenticated("LtI60OqaM/gZbaeN8tWBJqOy7yiPwxSMZDo/aQvsPFzbJiGUQZ2iyDtBkL/+GJseJnUweTabuOn8RtR4V3MOKw==");
    }

    /**
     * Provider data for testing
     *
     * @return array
     */
    protected function invalidBodyProvider(): array
    {
        $identity = [
            '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
            'id' => '3r5h8uka-18ec-496a-8713-8e6e5f065438',
            'node' => 'localhost',
            'signkeys' => [
                'default' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y', 
                'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
            ],
            'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
        ];

        $identity1 = $identity;
        unset($identity1['$schema']);

        $identity2 = $identity;
        $identity2['$schema'] = 'foo';

        $identity3 = $identity;
        unset($identity3['node']);

        return [
            ['body' => null, 'message' => 'body is not base58 encoded json'],
            ['body' => ['foo' => 'bar'], 'message' => 'body does not contain the $schema property'],
            ['body' => $identity1, 'message' => 'body does not contain the $schema property'],
            ['body' => $identity2, 'message' => "failed to extract resource: Unrecognized schema 'foo' for event '%hash'"],
            ['body' => $identity3, 'message' => 'node is required']
        ];
    }

    /**
     * Test adding event chain with event with invalid body
     *
     * @dataprovider invalidBodyProvider
     * @param  ApiTester            $I       
     * @param  \Codeception\Example $example
     */
    public function testInvalidBody(ApiTester $I, \Codeception\Example $example): void
    {
        $bodies = $this->mockEventBodies($example['body']);
        $data = $this->mockEventChainData($I, $bodies);

        $this->expectHttpRequest($I, 'http://legalflow/identities/', $bodies[0], $data['events'][0]);
        $this->doTest($I, $example, $data);
    }

    /**
     * Perform test actions
     *
     * @param ApiTester $I
     * @param \Codeception\Example $example 
     * @param array $data 
     */
    protected function doTest(ApiTester $I, \Codeception\Example $example, array $data): void
    {
        $hash = $data['events'][1]['hash'];
        $error = "event '{$hash}': " . str_replace('%hash', $hash, $example['message']);

        $I->haveHttpHeader('Content-Type', 'application/json');
                $I->sendPOST('/event-chains', $data);

        $I->expectTo('see error message');

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains($error);

        $I->expectTo('see that first event and then error event were added');

        $I->sendGET('/event-chains/' . $data['id']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['events' => ['hash' => $data['events'][0]['hash']]]);
        $I->dontSeeResponseContainsJson(['events' => ['hash' => $hash]]);
        $I->dontSeeResponseContainsJson(['events' => ['hash' => $data['events'][2]['hash']]]);
        $I->seeValidErrorEventInResponse(
            [$error], 
            [$data['events'][1], $data['events'][2]]
        );
    }

    /**
     * Get bodies of chain events
     *
     * @param array|null $invalidBody
     * @return array
     */
    protected function mockEventBodies(?array $invalidBody): array
    {
        return [
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
            $invalidBody,
            [
                '$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#',
                'id' => '4fd69b8e-6bd3-4d88-81dd-a6ed9308a14e',
                'node' => 'localhost',
                'signkeys' => [
                    'default' => 'BvEdG3ATxtmkbCVj9k2yvh3s6ooktBoSmyp8xwDqCQHp', 
                    'system' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
                ],
                'encryptkey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
            ]
        ];
    }

    /**
     * Create event chain data
     *
     * @param ApiTester $I
     * @param array $bodies 
     * @return array
     */
    protected function mockEventChainData(ApiTester $I, array $bodies): array
    {
        $chain = $I->createEventChain(3, $bodies);

        return $I->castChainToData($chain);
    }

    /**
     * Expect http request
     *
     * @param ApiTester $I
     * @param string $url 
     * @param array $data 
     * @param array $event 
     */
    protected function expectHttpRequest(ApiTester $I, string $url, array $data, array $event): void
    {
        $I->expectHttpRequest(function (Request $request) use ($I, $url, $data, $event) {
            $data['timestamp'] = $I->getTimeFromEvent($event);
            $json = json_encode($data);

            $I->assertEquals($url, (string)$request->getUri());
            $I->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
            $I->assertJsonStringEqualsJsonString($json, (string)$request->getBody());
            
            return new Response(200);
        });
    }
}
