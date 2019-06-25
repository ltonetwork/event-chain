<?php declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use ResourceInterface;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;
use Jasny\DB\Entity\Identifiable;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Extract and store the resource from the event. A resource might be a workflow action, an identity or
 * some asset. These assets may be stored at an external service. Identities are embedded in the event
 * chain.
 *
 * Some resource services, specifically the workflow engine can reject the new resource. In this case we can't
 * continue with added the rest of the events on the chain. Instead everything will halt. The HandleFailed action
 * collects subsequent actions and adds an error action to the chain.
 */
class StoreResource
{
    /**
     * @var \EventChain
     */
    protected $chain;

    /**
     * @var \ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \ResourceStorage
     */
    protected $resourceStorage;

    /**
     * StoreResource constructor.
     *
     * @param \EventChain      $chain
     * @param \ResourceFactory $factory
     * @param \ResourceStorage $storage
     */
    public function __construct(\EventChain $chain, \ResourceFactory $factory, \ResourceStorage $storage)
    {
        $this->chain = $chain;
        $this->resourceFactory = $factory;
        $this->resourceStorage = $storage;
    }

    /**
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @param ArrayObject      $newEvents
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation, ArrayObject $newEvents): Pipeline
    {
        return $pipeline->apply(function (\Event $event) use ($validation, $newEvents): void {
            if ($validation->failed()) {
                return;
            }

            try {
                $resource = $this->resourceFactory->extractFrom($event);
            } catch (\UnexpectedValueException $e) {
                $error = ValidationResult::error("failed to extract resource: %s", $e->getMessage());
                $validation->add($error, "event '$event->hash': ");
                return;
            }

            $authzValidation = $this->applyPrivilegeToResource($resource, $event);

            $validation->add($authzValidation, "event '$event->hash': ");
            $validation->add($resource->validate(), "event '$event->hash': ");

            if ($validation->failed()) {
                return;
            }

            $storedValidation = $this->storeResource($resource, $newEvents);
            $validation->add($storedValidation, "event '$event->hash': ");
        });
    }

    /**
     * Store a new event and add it to the chain
     *
     * @param ResourceInterface $resource
     * @param ArrayObject       $newEvents
     * @return ValidationResult
     */
    protected function storeResource(ResourceInterface $resource, ArrayObject $newEvents): ValidationResult
    {
        $newChain = $this->chain->withEvents($newEvents->getArrayCopy());

        try {
            $addedEvents = $this->resourceStorage->store($resource, $newChain);
        } catch (GuzzleException $e) {
            $id = 'ResourceInterface' . ($resource instanceof Identifiable ? ' ' . $resource->getId() : '');
            $reason = $e instanceof ClientException ? $e->getMessage() : 'Server error';

            return ValidationResult::error("Failed to store %s: %s", $id, $reason);
        }

        $this->chain->registerResource($resource);

        foreach ($addedEvents->events ?? [] as $event) {
            $newEvents->append($event);
        }

        return ValidationResult::success();
    }

    /**
     * Apply privilege to a resource.
     * Returns false if identity has no privileges to resource.
     *
     * @param \ResourceInterface $resource
     * @param \Event             $event
     * @return ValidationResult
     */
    public function applyPrivilegeToResource(\ResourceInterface $resource, \Event $event): ValidationResult
    {
        if (!$this->chain->hasEvents()) {
            return $resource instanceof \Identity ?
                ValidationResult::success() :
                ValidationResult::error("initial resource must be an identity");
        }

        $identities = $this->chain->identities->filterOnSignkey($event->signkey);
        $privileges = $identities->getPrivileges($resource);

        if ($privileges === []) {
            return ValidationResult::error("no privileges for event");
        }

        $resource->applyPrivilege($this->consolidatedPrivilege($resource, $privileges));

        return ValidationResult::success();
    }

    /**
     * Create a consolidated privilege from an array of privileges
     * @codeCoverageIgnore
     *
     * @param \ResourceInterface     $resource
     * @param \Privilege[]  $privileges
     * @return \Privilege
     */
    protected function consolidatedPrivilege(\ResourceInterface $resource, array $privileges): \Privilege
    {
        return \Privilege::create($resource)->consolidate($privileges);
    }
}
