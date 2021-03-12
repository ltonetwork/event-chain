<?php

declare(strict_types=1);

namespace AddEventStep;

use ArrayObject;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * Store ship data for blackbox project.
 */
class StoreShip
{
    protected \ShipStore $storage;

    /**
     * StoreResource constructor.
     */
    public function __construct(\ShipStore $storage)
    {
        $this->storage = $storage;
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

            $info = $event->getBody();

            switch ($info['$schema'] ?? '') {
                case 'https://dekimo.lto.network/ship':
                    $this->storage->storeShip($info);
                    break;
                case 'https://dekimo.lto.network/shipevent':
                    $this->storage->storeShipEvent($info);
                    break;
            }
        });
    }
}
