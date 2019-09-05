<?php declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\EntitySet;
use Jasny\DB\Entity\Identifiable;
use Jasny\ValidationResult;
use LTO\Account;
use LTO\EventChain as LTOEventChain;
use function LTO\sha256;
use function sodium_crypto_generichash as blake2b;

/**
 * EventChain entity
 */
class EventChain extends MongoDocument
{
    /**
     * Unique identifier
     * @var string
     */
    public $id;
    
    /**
     * List of event
     * @var \Jasny\DB\EntitySet|Event[]
     * @snapshot
     */
    public $events;

    /**
     * Projected identities
     * @var IdentitySet|Identity[]
     * @snapshot
     */
    public $identities;
    
    /**
     * Resources that are part of this chain.
     * @var ExternalResource&EntitySet
     */
    public $resources = [];

    /**
     * The hash of the previous event not in this partial chain.
     * @var string
     */
    protected $partialPrevious;

    /**
     * Cast to data.
     *
     * @return array
     */
    public function toData()
    {
        $data = parent::toData();

        foreach ($data['events'] as &$event) {
            unset($event['cachedBody']);
        }

        return $data;
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->events = $this->events ?? EntitySet::forClass(Event::class);
        $this->identities = $this->identities ?? IdentitySet::forClass(Identity::class);

        parent::__construct();
    }


    /**
     * Get the initial hash which is based on the event chain id.
     *
     * @return string
     */
    public function getInitialHash(): string
    {
        $rawId = base58_decode($this->id);
        
        return base58_encode(hash('sha256', $rawId, true));
    }
    
    /**
     * Get the latest hash.
     * Expecting a new event to use this as previous property.
     *
     * @return string
     */
    public function getLatestHash(): string
    {
        return count($this->events) > 0
            ? $this->getLastEvent()->hash
            : ($this->partialPrevious ?? $this->getInitialHash());
    }
    
    /**
     * Get the first event of the chain.
     *
     * @param bool $allowPartial
     * @return Event
     * @throws UnderflowException
     * @throws OutOfBoundsException
     */
    public function getFirstEvent(bool $allowPartial = false): Event
    {
        if (count($this->events) === 0) {
            throw new UnderflowException("chain has no events");
        }

        if (!$allowPartial && $this->isPartial()) {
            throw new OutOfBoundsException("partial chain doesn't hold the first event");
        }

        return $this->events[0];
    }
    
    /**
     * Get the last event of the chain.
     *
     * @return Event
     * @throws UnderflowException
     */
    public function getLastEvent(): Event
    {
        if (count($this->events) === 0) {
            throw new UnderflowException("chain has no events");
        }
        
        return $this->events[count($this->events) - 1];
    }
    
    
    /**
     * Get the nodes of the identities
     *
     * @return string[]
     */
    public function getNodes(): array
    {
        return isset($this->identities) ? $this->identities->__get('node') : [];
    }
    
    /**
     * Get the nodes of the identities matching system sign key
     *
     * @param string $signKey
     * @return string[]
     */
    public function getNodesForSystem(string $signKey): array
    {
        return Pipeline::with($this->identities)
            ->filter(function (Identity $identity) use ($signKey) {
                return isset($identity->signkeys['system']) && $identity->signkeys['system'] === $signKey;
            })
            ->column('node')
            ->unique()
            ->toArray();
    }

    /**
     * Get the nodes of the identities matching user sign key
     *
     * @param string $signKey
     * @return string[]
     */
    public function getNodesForUser(string $signKey): array
    {
        return Pipeline::with($this->identities)
            ->filter(function (Identity $identity) use ($signKey) {
                return isset($identity->signkeys['default']) && $identity->signkeys['default'] === $signKey;
            })
            ->column('node')
            ->unique()
            ->toArray();
    }

    /**
     * Check if the gives node corresponds with the sign key.
     *
     * @param string $signKey
     * @param string $node
     * @return bool
     */
    public function hasNodesForUserAndSystem($signKey, $node): bool
    {
        $nodes = array_merge($this->getNodesForUser($signKey), $this->getNodesForSystem($signKey));

        return in_array($node, $nodes, true);
    }

    /**
     * Check if the chain has identity which belongs to a given node sign key.
     *
     * @param string $userSignKey
     * @param string $nodeSignKey
     * @return bool
     */
    public function hasSystemKeyForIdentity(string $userSignKey, string $nodeSignKey): bool
    {
        return Pipeline::with($this->identities)
            ->hasAny(function (Identity $identity) use ($userSignKey, $nodeSignKey) {
                return
                    isset($identity->signkeys['default']) && $identity->signkeys['default'] == $userSignKey &&
                    isset($identity->signkeys['system']) && $identity->signkeys['system'] == $nodeSignKey;
            });
    }
    
    /**
     * Check if the event is signed by the account
     *
     * @param Event   $event
     * @param Account $account
     * @return bool
     */
    public function isEventSignedByAccount(Event $event, Account $account): bool
    {
        $accountKey = $account->getPublicSignKey();
        
        if ($event->signkey === $accountKey) {
            return true;
        }

        if ($this->hasSystemKeyForIdentity($event->signkey, $accountKey)) {
            return true;
        }

        return false;
    }
    
    /**
     * Check if the event is sent from the node of one of the identities
     *
     * @param Event       $event
     * @param string|null $node
     * @return bool
     */
    public function isEventSignedByIdentityNode(Event $event, ?string $node = null): bool
    {
        $node = $node ?? $event->origin;

        return isset($node) && $this->hasNodesForUserAndSystem($event->signkey, $node);
    }
    
    
    /**
     * Check if this chain has the genesis event or is empty.
     *
     * @return bool
     */
    public function isPartial(): bool
    {
        return count($this->events) > 0
            ? $this->events[0]->previous !== $this->getInitialHash()
            : $this->partialPrevious !== null;
    }
    
    /**
     * Check if the chain has events.
     *
     * @return bool
     */
    public function hasEvents(): bool
    {
        return count($this->events) !== 0;
    }

    /**
     * Check if id is valid
     *
     * @return bool
     */
    public function isValidId(): bool
    {
        $decodedId = base58_decode($this->id);
        
        if (strlen($decodedId) !== 45) {
            return false;
        }

        $firstEvent = $this->getFirstEvent();
        
        $signkey = base58_decode($firstEvent->signkey);
        $signkeyHashed = sha256(blake2b($signkey));
        
        $vars = unpack('Ctype/a20nonce/a20keyhash/a4checksum', $decodedId);
        
        return
            $vars['type'] === LTOEventChain::CHAIN_ID &&
            $vars['keyhash'] === substr($signkeyHashed, 0, 20) &&
            $vars['checksum'] === substr($decodedId, -4);
    }
    
    /**
     * Validate the chain
     *
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $validation = parent::validate();
        
        if (count($this->events) === 0) {
            $validation->addError('no events');
        } else {
            $invalidId = 
                !$this->isPartial() && 
                $this->getFirstEvent()->previous === $this->getInitialHash() && 
                !$this->isValidId();

            if ($invalidId) {
                $validation->addError('invalid id');
            }
        }
        
        $validation->add($this->validateIntegrity());
        
        return $validation;
    }
    
    /**
     * Validate chain integrity
     *
     * @return ValidationResult
     */
    protected function validateIntegrity(): ValidationResult
    {
        $validation = new ValidationResult();
        $previous = null;

        foreach ($this->events as $event) {
            if (isset($previous) && $event->previous !== $previous) {
                $msg = "broken chain; previous of '%s' is '%s', expected '%s'";
                $validation->addError($msg, $event->hash, $event->previous, $previous);
                break;
            }

            $previous = $event->hash;
        }
        
        return $validation;
    }

    /**
     * Return an event chain without any events
     *
     * @return static
     */
    public function withoutEvents(): self
    {
        $emptyChain = new static();
        $emptyChain->id = $this->id;
        
        return $emptyChain;
    }

    /**
     * Return a partial event chain with the new events.
     *
     * @param Event[] $events
     * @return static
     */
    public function withEvents(array $events): self
    {
        $chain = clone $this;
        $chain->events = EntitySet::forClass(Event::class, $events);

        return $chain;
    }

    /**
     * Return an event chain without identities and resources
     *
     * @return static
     */
    public function onlyWithEvents(): self
    {
        $chain = clone $this;
        
        $chain->identities = [];
        $chain->resources = [];

        return $chain;
    }    
    
    /**
     * Get all events that follow the specified event.
     *
     * @param string $hash
     * @return Event[]
     * @throws OutOfBoundsException if event can't be found
     */
    public function getEventsAfter(string $hash): array
    {
        $events = !$this->isPartial() && $this->getInitialHash() === $hash ? [] : null;

        foreach ($this->events as $event) {
            if (isset($events)) {
                $events[] = $event;
            } elseif ($event->hash === $hash) {
                $events = [];
            }
        }
        
        if ($events === null) {
            throw new OutOfBoundsException(
                "Event '$hash' not found" . ($this->isPartial() ? ' in partial chain' : '')
            );
        }
        
        return $events;
    }
    
    /**
     * Get a partial chain consisting of all events that follow the specified event.
     *
     * @param string $hash
     * @return EventChain
     * @throws OutOfBoundsException if event can't be found
     */
    public function getPartialAfter(string $hash): EventChain
    {
        $partial = $this->withEvents($this->getEventsAfter($hash));
        $partial->partialPrevious = $this->getLatestHash();

        return $partial;
    }

    /**
     * Get a partial chain without any events.
     *
     * @return EventChain
     */
    public function getPartialWithoutEvents(): EventChain
    {
        $partial = $this->withEvents([]);
        $partial->partialPrevious = $this->getLatestHash();

        return $partial;
    }

    
    /**
     * Register that a resource is used in this chain
     *
     * @param ResourceInterface $resource
     */
    public function registerResource(ResourceInterface $resource): void
    {
        if ($resource instanceof Identity) {
            $this->identities->set($resource);
            return;
        }

        if ($resource instanceof ExternalResource) {
            $this->resources->add($resource);
        }
    }

    /**
     * Called when entity is cloned
     */
    public function __clone()
    {
        $this->events = clone $this->events;
    }

    /**
     * Prepare result when casting object to JSON
     *
     * @return object
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();
        $result->latestHash = $this->getLatestHash();

        return $result;
    }

    /**
     * Convert a Jasny DB styled filter to a MongoDB query.
     *
     * @param array $filter
     * @param array $opts
     * @return array
     */
    protected static function filterToQuery($filter, array $opts = [])
    {
        $query = parent::filterToQuery($filter, $opts);

        if (isset($filter['chains_for'])) {
            unset($query['chains_for']);

            $query['$or'] = [
                ['identities.signkeys.default' => $filter['chains_for']],
                ['identities.signkeys.system' => $filter['chains_for']]
            ];
        }

        return $query;
    }
}
