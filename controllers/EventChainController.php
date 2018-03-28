<?php

use LTO\AccountFactory;

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
     * Class constructor
     */
    public function __construct()
    {
        $this->resourceFactory = new ResourceFactory();
        $this->resourceStorage = new ResourceStorage(App::config()->endpoints, App::httpClient());
    }

    /**
     * Add a new chain or new events to an existing chain
     */
    public function add()
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
        
        return $this->ok();
    }
    
    public function showList()
    {
        $account = 
        
        $events = EventChain::fetchAll();
        
        $this->output($list, 'json');
    }
}
