<?php

use LTO\Account;
use Psr\Container\ContainerInterface;

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
     * @var Dispatcher
     */
    protected $dispatcher;
    
    /**
     * Account that signed the request
     * @var Account
     */
    protected $account;

    /**
     * Account of the node
     * @var Account
     */
    protected $nodeAccount;


    /**
     * Class constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->resourceFactory = $container->get('models:resources.factory');
        $this->resourceStorage = $container->get('models:resources.storage');
        $this->dispatcher = $container->get('models:dispatcher.client');
        $this->nodeAccount = $container->get('node.account');
    }

    /**
     * Before each action
     */
    public function before()
    {
    }


    /**
     * List all the event chains the authorized user is an identity in
     */
    public function listAction()
    {
        $this->byDefaultSerializeTo('json');
        $this->account = $this->getRequest()->getAttribute('account');

        if (!isset($this->account)) {
            $this->requireAuth();
            $this->output('http request not signed', 'text/plain');
            $this->cancel();
        }

        $events = EventChain::fetchAll(['identities.signkeys.user' => $this->account->getPublicSignKey()]);

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
        
        $dispatcher = new DispatcherManager($this->dispatcher, $this->nodeAccount, $this->resourceFactory);
        $manager = new EventManager($chain, $this->resourceFactory, $this->resourceStorage, $dispatcher);
        $handled = $manager->add($newChain);
        
        if ($handled->failed()) {
            App::debug($handled->getErrors());
            return $this->badRequest(json_encode($handled->getErrors()));
        }
        
        $this->output($chain, 'json');
        return $this->ok();
    }
    
    /**
     * Output a single event chain
     * 
     * @param string $id
     */
    public function getAction($id)
    {
        $event = EventChain::fetch($id);
        
        if (!isset($event)) {
            return $this->notFound("Event not found");
        }

        $this->output($event, 'json');
    }

    /**
     * Delete an event chain
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $event = EventChain::fetch($id);

        if (isset($event))
            $event->delete();

        $this->noContent();
    }
}
