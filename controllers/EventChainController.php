<?php

use LTO\AccountFactory;
use LTO\HTTPSignature;
use LTO\HTTPSignatureException;

/**
 * Event chain controller
 */
class EventChainController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;
    
    /**
     * @var AccountFactory
     */
    protected $accountFactory;
    
    /**
     * @var ResourceFactory 
     */
    protected $resourceFactory;
    
    /**
     * @var ResourceStorage
     */
    protected $resourceStorage;
    
    /**
     * Account that signed the request
     * @var Account
     */
    protected $account;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->resourceFactory = new ResourceFactory();
        $this->resourceStorage = new ResourceStorage(Jasny\arrayify(App::config()->endpoints), App::httpClient());
        
        $this->accountFactory = App::getContainer()->get(AccountFactory::class);
    }

    /**
     * Before each action
     */
    public function before()
    {
        $requiredHeaders = $this->isPostRequest()
            ? ['(request-target)', 'date', 'content-type', 'content-length', 'digest']
            : ['(request-target)', 'date'];

        $httpSignature = new HTTPSignature($this->getRequest(), $requiredHeaders);

        try {
            $httpSignature->useAccountFactory($this->accountFactory)->verify();
            $this->account = $httpSignature->getAccount();
        } catch (HTTPSignatureException $e) {
            $this->setResponseHeader(
                "WWW-Authenticate",
                sprintf('Signature algorithm="ed25519-sha256",headers="%s"', join(' ', $requiredHeaders))
            );
            
            $this->output($e->getMessage(), 'text/plain');
            $this->requireAuth();
            
            $this->cancel();
        }
    }
    
    /**
     * List all the event chains the authorized user is an identity in
     */
    public function listAction()
    {
        $events = EventChain::fetchAll(['identity' => $this->account]);
        
        $this->output($events, 'json');
    }
    
    /**
     * Add a new chain or new events to an existing chain
     */
    public function addAction()
    {
        $data = $this->getInput();
        
        $newChain = EventChain::create()->setValues($data);
        $validation = $newChain->validate();
        
        if ($validation->failed()) {
            return $this->badRequest($validation->getErrors());
        }
        
        $chain = EventChain::fetch($newChain->id) ?: $newChain->withoutEvents();
        
        $manager = new EventManager($chain, $this->resourceFactory, $this->resourceStorage);
        $handled = $manager->add($newChain);
        
        if ($handled->failed()) {
            return $this->badRequest($validation->getErrors());
        }
        
        $this->output($chain);
        return $this->ok();
    }
    
}
