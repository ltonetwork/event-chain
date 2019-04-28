<?php
namespace Helper;

use PHPUnit\Framework\Assert;
use Codeception\PHPUnit\Constraint\JsonContains;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface as Message;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class WorkflowIntegration extends Api
{
    /**
     * Http response
     * @var GuzzleHttp\Psr7\Response
     **/
    protected $response;

    /**
     * Response body
     * @var string
     **/
    protected $responseBody;

    /**
     * Send post request
     * 
     * @param  string $url
     * @param  array $data
     * @return GuzzleHttp\Psr7\Response
     */
    public function sendPOST($url, $data)
    {
        $options = [
            'http_errors' => true,
            'json' => $data
        ];

        $headers = [
            'Digest' => 'SHA-256=' . base64_encode(hash('sha256', json_encode($data), true))
        ];

        $client = new \GuzzleHttp\Client();
        $request = new Request('POST', $url, $headers);

        $this->debugRequest($request, $data);

        $this->response = $client->send($request, $options);
        $this->responseBody = (string)$this->response->getBody();

        $this->debugResponse($this->response, $this->responseBody);
    }

    /**
     * Check response code
     */
    public function seeResponseCodeIs($code)
    {
        Assert::assertSame($code, $this->response->getStatusCode());
    }

    /**
     * Check that response is json
     */
    public function seeResponseIsJson()
    {
        $header = $this->response->getHeaderLine('Content-Type');
        $isJson = (bool)preg_match('|\bapplication/json\b|', $header);

        Assert::assertTrue($isJson, 'application/json    response header is not set');
        Assert::assertNotEquals('', $this->responseBody, 'response is empty');

        json_decode($this->responseBody);
        $errorCode = json_last_error();
        $errorMessage = json_last_error_msg();

        Assert::assertEquals(
            JSON_ERROR_NONE,
            $errorCode,
            sprintf("Invalid json: %s. System message: %s.", $this->responseBody, $errorMessage)
        );
    }

    /**
     * See that response contains event chain
     */
    public function seeResponseContainsEventChain()
    {
        $response = $this->getResponseJson();

        Assert::assertTrue(isset($response->id));
        Assert::assertTrue(isset($response->events));
    }

    /**
     * See that response event chain contains given amount of events
     *
     * @param int $count 
     */
    public function seeEventChainHasEventsCount($count)
    {
        $response = $this->getResponseJson();

        Assert::assertCount($count, $response->events);
    }

    /**
     * Check contents of event body
     *
     * @param int $idx
     * @param string $type 
     * @param string|array $data 
     */
    public function seeEventBodyIs($idx, $type, $data)
    {
        $response = $this->getResponseJson();
        $event = $response->events[$idx];
        $body = $this->decodeEventBody($event->body);

        $isSchemaValid = is_schema_link_valid($body['$schema'], $type);      
        Assert::assertTrue($isSchemaValid);

        if (is_string($data)) {
            Assert::assertSame($data, $body['id']);
        } else {
            Assert::assertThat(json_encode($body), new JsonContains($data));
        }
    }

    /**
     * Get response json
     *
     * @return object
     */
    protected function getResponseJson(): \stdClass
    {
        return json_decode($this->responseBody);
    }

    /**
     * Debug http request
     *
     * @param Request $request
     * @param array $data 
     */
    protected function debugRequest(Request $request, array $data)
    {        
        codecept_debug($request->getMethod() . ' ' . $request->getUri());
        $this->debugHeaders($request);
        codecept_debug(json_encode($data));
    }

    /**
     * Debug http response
     *
     * @param GuzzleHttp\Psr7\Response $response
     * @param string $responseBody 
     */
    protected function debugResponse(Response $response, string $responseBody)
    {        
        codecept_debug($this->response->getStatusCode() . ' ' . $this->response->getReasonPhrase());
        $this->debugHeaders($this->response);
        codecept_debug($this->responseBody);
    }

    /**
     * Debug http message headers
     *
     * @param Psr\Http\Message\MessageInterface $message
     */
    protected function debugHeaders(Message $message)
    {
        $headers = $message->getHeaders();

        foreach ($headers as $name => $value) {
            codecept_debug($name . ': ' . $message->getHeaderLine($name));
        }
    }
}
