<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\ResponseInterface as Response;
use LTO\HTTPSignature;
use LTO\AccountFactory;
use LTO\Account;
use LTO\HTTPSignatureException;
use function Jasny\str_starts_with;

/**
 * Description of HTTPSignatureMiddleware.
 */
class HTTPSignatureMiddleware
{
    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * Rewrite request path.
     * @var string
     */
    protected $baseRewrite;

    /**
     * @var Account
     */
    public $account;


    /**
     * Class constructor.
     *
     * @param AccountFactory $accountFactory
     * @param string|null    $baseRewrite
     */
    public function __construct(AccountFactory $accountFactory, ?string $baseRewrite = null)
    {
        $this->accountFactory = $accountFactory;
        $this->baseRewrite = $baseRewrite ?? '';
    }


    /**
     * Invoke middleware.
     *
     * @param ServerRequest  $request
     * @param Response       $response
     * @param callable       $next
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, callable $next): Response
    {
        $nextResponse = $this->isRequestSigned($request)
            ? $this->handle($request, $response, $next)
            : $next($request, $response);

        if ($nextResponse->getStatusCode() === 401 && !$nextResponse->hasHeader("www-authenticate")) {
            $requiredHeaders = $this->getRequiredHeaders($request);
            
            $nextResponse = $nextResponse->withHeader(
                "WWW-Authenticate",
                sprintf('Signature algorithm="ed25519-sha256",headers="%s"', join(' ', $requiredHeaders))
            );
        }
        
        return $nextResponse;
    }

    /**
     * Handle signed request.
     *
     * @param ServerRequest  $request
     * @param Response       $response
     * @param callable       $next
     * @return Response
     */
    public function handle(ServerRequest $request, Response $response, callable $next): Response
    {
        $requiredHeaders = $this->getRequiredHeaders($request);

        $signatureRequest = $this->baseRewrite($request);
        $httpSignature = new HTTPSignature($signatureRequest, $requiredHeaders);

        try {
            $httpSignature->useAccountFactory($this->accountFactory)->verify();
            $this->account = $httpSignature->getAccount();
            $nextRequest = $request->withAttribute('account', $this->account);
        } catch (HTTPSignatureException $e) {
            $response->getBody()->write($e->getMessage());

            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'text/plain');
        }

        return $next($nextRequest, $response);
    }


    /**
     * Get the request headers.
     *
     * @todo Check headers on POST request. This has been disabled, because of issues on the client.
     *
     * @param ServerRequest $request
     * @return array
     */
    protected function getRequiredHeaders(ServerRequest $request): array
    {
        return /*$request->getMethod() === 'POST'
            ? ['(request-target)', 'date', 'content-type', 'content-length', 'digest']
            :*/ ['(request-target)', 'date'];
    }

    /**
     * Check if the request contains a signature authorization header.
     *
     * @return bool
     */
    protected function isRequestSigned(ServerRequest $request): bool
    {
        return
            $request->getAttribute('account') === null &&
            $request->hasHeader('authorization') &&
            str_starts_with(strtolower($request->getHeaderLine('authorization')), 'signature ');
    }

    /**
     * Rewrite the path (in case of proxy).
     *
     * @param ServerRequest $request
     * @return ServerRequest
     */
    protected function baseRewrite(ServerRequest $request): ServerRequest
    {
        if ($this->baseRewrite !== '') {
            $uri = $request->getUri();
            $newUri = $uri->withPath($this->baseRewrite . $uri->getPath());
            $request = $request->withUri($newUri);
        }

        return $request;
    }
}
