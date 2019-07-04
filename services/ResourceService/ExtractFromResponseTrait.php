<?php

declare(strict_types=1);

namespace ResourceService;

use EventChain;
use GuzzleHttp\Psr7\Response as HttpResponse;

/**
 * Trait to extract event chain from HTTP response.
 */
trait ExtractFromResponseTrait
{
    /**
     * Get event chain from http queries responses.
     *
     * @todo The response should include a `$schema` so we don't have to do duck typing.
     *
     * @param HttpResponse $response
     * @param EventChain   $chain
     * @return EventChain|null
     */
    protected function getEventsFromResponse(HttpResponse $response): ?EventChain
    {
        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            return null;
        }

        $body = (string)$response->getBody();
        $data = json_decode($body, true);
        $isValidJson = isset($data['id']) && isset($data['events']);

        if (!$isValidJson) {
            $error = json_last_error_msg();
            if (strtolower($error) === 'no error') {
                $error = "data: $body";
            }

            trigger_error("Invalid JSON response: $error", E_USER_WARNING);
            return null;
        }

        return EventChain::fromData($data);
    }
}
