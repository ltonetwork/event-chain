<?php declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Trigger a warning on an unexpected HTTP error (status code >= 400).
 * Such a warning will most likely lead to an Exception. The warning will help in debugging.
 *
 * @todo Turn this into middleware
 */
class HttpErrorWarning
{
    /**
     * @var int[]
     */
    protected $notOn = [];

    /**
     * Disable warnings for these HTTP response codes.
     *
     * @param int ...$statusCodes
     * @return static
     */
    public function notOn(int ...$statusCodes): self
    {
        $clone = clone $this;
        $clone->notOn = array_merge($this->notOn, $statusCodes);

        return $clone;
    }

    /**
     * Invoke the service
     *
     * @param Response $response
     * @parma string   $url
     * @return void
     */
    public function __invoke(Response $response, string $url): void
    {
        if ($response->getStatusCode() >= 400 && !in_array($response->getStatusCode(), $this->notOn, true)) {
            $this->onError($response, $url);
        }
    }

    /**
     * Handler an error.
     *
     * @param Response $response
     * @param string $url
     */
    protected function onError(Response $response, string $url): void
    {
        $status = $response->getStatusCode() . ' ' . $response->getReasonPhrase();

        $hasMessage = $response->getStatusCode() < 500
            && preg_match('~^(text/plain|application/json)(;|$)~', $response->getHeaderLine('Content-Type'));
        $message = $hasMessage ? ': ' . $response->getBody() : '';

        trigger_error("POST $url resulted in a `$status` response" . $message, E_USER_WARNING);
    }
}
