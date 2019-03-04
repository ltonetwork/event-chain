<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Detect proxy and handle forwarded url.
 * This is needed for http signature validation and redirects.
 *
 * @todo Fix this and move to own library.
 * @todo Check for trusted ip.
 */
class ProxyDetection
{
    function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!$request->hasHeader('X-Original-Uri')) {
            return $next($request, $response);
        }

        $originalUri = $request->getHeaderLine('X-Original-Uri');
        $requestUri = (string)$request->getUri()->withScheme('')->withHost('')->withPort(null);

        if (!str_ends_with($requestUri, $originalUri)) {
            trigger_error("Proxy isn't set to application root", E_USER_WARNING);

            $body = clone $response->getBody();
            $body->write("proxy isn't set to application root");

            return $response
                ->withStatus(404)
                ->withHeader('Content-Type','text/plain')
                ->withBody($body);
        }

        // Should be a uri instead of a string and also with forwarded host and port.
        $request = $request->withAttribute('original_uri', $originalUri);

        return $next($request, $response);
    }
}
