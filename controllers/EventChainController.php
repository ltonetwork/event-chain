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
     * @var EventFactory
     */
    protected $eventFactory;
    
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
     *
     * @param ResourceFactory    $resourceFactory  "models.resources.factory"
     * @param ResourceStorage    $resourceStorage  "models.resources.storage"
     * @param DispatcherManager  $dispatcher       "models.dispatcher.manager"
     * @param EventFactory       $eventFactory     "models.events.factory"
     * @param Accout             $nodeAccount      "node.account"
     */
    public function __construct(
        ResourceFactory $resourceFactory, ResourceStorage $resourceStorage, Dispatcher $dispatcher,
        EventFactory $eventFactory, Account $nodeAccount
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceStorage = $resourceStorage;
        $this->dispatcher = $dispatcher;
        $this->eventFactory = $eventFactory;
        $this->nodeAccount = $nodeAccount;
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
        
        $manager = new EventManager(
            $chain, $this->resourceFactory, $this->resourceStorage, $this->dispatcher, $this->eventFactory
        );
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
     * @param string $id
     */
    public function deleteAction($id)
    {
        $event = EventChain::fetch($id);

        if (isset($event)) {
            $event->delete();
        }

        $this->noContent();
    }
}
