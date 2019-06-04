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

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id'])) {
            trigger_error("Invalid JSON response: " . json_last_error_msg(), E_USER_WARNING);
            return null;
        }

        return isset($data['id']) && isset($data['events']) ? EventChain::fromData($data) : null;
    }
}
