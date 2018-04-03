<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use LTO\HTTPSignature;
use LTO\AccountFactory;

/**
 * Description of HTTPSignatureMiddleware
 *
 * @author arnold
 */
class HTTPSignatureMiddleware
{
    /**
     * @var AccountFactory
     */
    protected $accountFactory;
    
    
    /**
     * Class constructor
     * 
     * @param AccountFactory $accountFactory
     */
    public function __construct(AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
    }
    
    /**
     * Get the request headers
     * 
     * @param RequestInterface $request
     * @return array
     */
    protected function getRequiredHeaders(ServerRequestInterface $request)
    {
        return /*$request->getMethod() === 'POST'
            ? ['(request-target)', 'date', 'content-type', 'content-length', 'digest']
            :*/ ['(request-target)', 'date'];
    }
    
    /**
     * Handle signed request
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface       $response
     * @param callable                $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $requiredHeaders = $this->getRequiredHeaders($request);
    
        $httpSignature = new HTTPSignature($request, $requiredHeaders);

        try {
            $httpSignature->useAccountFactory($this->accountFactory)->verify();
            
            $this->account = $httpSignature->getAccount();
            $nextRequest = $request->withAttribute('account', $httpSignature);
        } catch (HTTPSignatureException $e) {
            $response->getBody()->write($e->getMessage());
            
            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'text/plain');
        }
        
        return $next($nextRequest, $response);
    }
    
    /**
     * Invoke middleware
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface       $response
     * @param callable                $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (
            $request->getAttribute('account') === null &&
            $request->hasHeader('authorization') &&
            jasny\str_starts_with(strtolower($request->getHeaderLine('authorization')), 'signature ')
        ) {
            $nextResponse = $this->handle($request, $response, $next);
        } else {
            $nextResponse = $next($request, $response);
        }
        
        if ($nextResponse->getStatusCode() === 401 && !$nextResponse->hasHeader("www-authenticate")) {
            $requiredHeaders = $this->getRequiredHeaders($request);
            
            $nextResponse = $nextResponse->withHeader(
                "WWW-Authenticate",
                sprintf('Signature algorithm="ed25519-sha256",headers="%s"', join(' ', $requiredHeaders))
            );
        }
        
        return $nextResponse;
    }
}
