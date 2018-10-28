<?php

use LTO\Account;

/**
 * CRUD controller for event chains.
 * Minus the C(reate) and U(update), which are handled in the Event controller. So only RD really.
 */
class EventChainController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * @var Gateway
     */
    protected $eventChains;

    /**
     * Account that signed the request
     * @var Account
     */
    protected $account;

    /**
     * EventChainController constructor.
     *
     * @param Gateway $eventChainGateway  "models.event-chains"
     */
    public function __construct(Gateway $eventChainGateway)
    {
        $this->eventChains = $eventChainGateway;
    }

    /**
     * Before each action
     */
    public function before()
    {
        $this->byDefaultSerializeTo('json');

        $this->account = $this->getRequest()->getAttribute('account');

        if (!isset($this->account)) {
            $this->requireAuth();
            $this->output('http request not signed', 'text/plain');
            $this->cancel();
        }
    }


    /**
     * List all the event chains the authorized user is an identity in.
     */
    public function listAction()
    {
        $eventChains = $this->eventChains->fetchAll([
            'identities.signkeys.user' => $this->account->getPublicSignKey()
        ]);

        $this->output($eventChains, 'json');
    }

    /**
     * Output a single event chain.
     * 
     * @param string $id
     */
    public function getAction($id)
    {
        $eventChain = $this->eventChains->fetch([
            'id' => $id,
            'identities.signkeys.user' => $this->account->getPublicSignKey()
        ]);
        
        if (!isset($eventChain)) {
            return $this->notFound("Event not found");
        }

        $this->output($eventChain, 'json');
    }

    /**
     * Delete an event chain.
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $eventChain = $this->eventChains->fetch([
            'id' => $id,
            'identities.signkeys.user' => $this->account->getPublicSignKey()
        ]);

        if (isset($eventChain)) {
            $eventChain->delete();
        }

        $this->noContent();
    }
}
