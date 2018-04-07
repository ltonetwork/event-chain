<?php

use LTO\Account;

/**
 * Event chain controller
 */
class EventChainController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;
    
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
    }

    /**
     * Before each action
     */
    public function before()
    {
//        $this->byDefaultSerializeTo('json');
//        $this->account = $this->getRequest()->getAttribute('account');
//        
//        if (!isset($this->account)) {
//            $this->requireAuth();
//            $this->output('http request not signed', 'text/plain');
//            $this->cancel();
//        }
    }
    
    /**
     * List all the event chains the authorized user is an identity in
     */
    public function listAction()
    {
        $events = EventChain::fetchAll(['identity' => $this->account->getPublicSignKey()]);
        
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
            return $this->badRequest($handled->getErrors());
        }
        
        $this->output($chain, 'json');
        return $this->ok();
    }
    
}
