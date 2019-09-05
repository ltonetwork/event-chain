<?php declare(strict_types=1);

use LTO\Account;

/**
 * CRUD controller for event chains.
 * Minus the C(reate) and U(update), which are handled in the Event controller. So only RD really.
 */
class EventChainController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * @var EventChainGateway
     */
    protected $eventChains;

    /**
     * @var bool
     */
    protected $allowReset;

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
     * EventChainController constructor.
     *
     * @param EventChainGateway $eventChainGateway  "models.event_chains"
     * @param bool|null         $allowReset
     * @param ResourceStorage   $resourceStorage
     */
    public function __construct(
        EventChainGateway $eventChainGateway,
        ?bool $allowReset,
        ResourceStorage $resourceStorage
    ) {
        $allowReset = $allowReset ?? false;
        object_set_dependencies($this, get_defined_vars());
    }

    /**
     * Before each action
     */
    public function before(): void
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
    public function listAction(): void
    {
        $eventChains = $this->eventChains->fetchAll([
            'identities.signkeys.default' => $this->account->getPublicSignKey()
        ]);

        $this->output($eventChains, 'json');
    }

    /**
     * Output a single event chain.
     *
     * @param string $id
     */
    public function getAction($id): void
    {
        $eventChain = $this->eventChains->fetch([
            'id' => $id,
            'chains_for' => $this->account->getPublicSignKey()
        ]);
        
        if (!isset($eventChain)) {
            $this->notFound("Event chain not found");
            return;
        }

        $this->output($eventChain, 'json');
    }

    /**
     * Delete an event chain.
     *
     * @param string $id
     */
    public function deleteAction($id): void
    {
        $signKey = $this->account->getPublicSignKey();

        $eventChain = $this->eventChains->fetch([
            'id' => $id,
            'identities.signkeys.default' => $signKey
        ]);

        if (!isset($eventChain)) {
            $this->notFound("Event chain not found");
            return;
        }

        $this->resourceStorage->delete($eventChain->resources, $signKey);
        $this->eventChains->delete($eventChain);

        $this->noContent();
    }

    /**
     * Delete all event chains (of this identity).
     */
    public function resetAction(): void
    {
        if (!$this->allowReset) {
            $this->notFound();
            return;
        }

        $signKey = $this->account->getPublicSignKey();

        $eventChains = $this->eventChains->fetchAll([
            'identities.signkeys.default' => $signKey,
        ]);

        foreach ($eventChains as $eventChain) {
            $this->resetChain->deleteResources($eventChain, $signKey);
            $this->eventChains->delete($eventChain);
        }

        $this->noContent();
    }
}
