<?php

declare(strict_types=1);

use LTO\Account;
use LTO\Event;
use LTO\EventChain;

/**
 * Create an event chain for a ship and add events to ship event chains.
 */
class ShipSync
{
    protected PDO $mysql;
    protected string $prefix;
    protected string $node;

    public function __construct(PDO $mysql, string $prefix, string $node)
    {
        $this->mysql = $mysql;
        $this->prefix = $prefix;
        $this->node = $node;
    }

    /**
     * Fetch the ship codes
     *
     * @return string[]
     */
    public function fetchShipCodes(): array
    {
        return $this->mysql->query("CALL IP_sel_ShipCode(@err)")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the event chain id.
     *
     * @param Account $account
     * @param string $shipCode
     * @return string
     */
    public function getEventChainId(Account $account, string $shipCode): string
    {
        return $account->createEventChain("{$this->prefix}:{$shipCode}")->id;
    }

    /**
     * Create a new event chain for a ship.
     */
    public function createEventChain(Account $account, string $shipCode, array $recipients = []): EventChain
    {
        $chain = $account->createEventChain("{$this->prefix}:{$shipCode}");
        $chain->add($this->createIdentityEvent($account, $this->node))->signWith($account);

        foreach ($recipients as $recipient) {
            $chain->add($this->createIdentityEvent($recipient, $recipient['node']))->signWith($account);
        }

        $chain->add($this->createNewShipEvent($shipCode))->signWith($account);

        return $chain;
    }

    /**
     * Create an Identity for an account and wrap it in an Event.
     */
    public function createIdentityEvent(Account $account, string $node)
    {
        $identity = new Identity();
        $identity->id = $account->getAddress();
        $identity->node = $node;
        $identity->signkeys = [
            'default' => $account->getPublicSignKey(),
            'system' => $account->getPublicSignKey(),
        ];

        return new Event(
            ['$schema' => 'https://specs.livecontracts.io/v0.2.0/identity/schema.json#']
            + $identity->getValues()
        );
    }

    /**
     * Fetch ship info and wrap it in an Event.
     */
    public function createNewShipEvent(string $shipCode)
    {
        $stmt = $this->mysql->prepare("CALL IP_sel_SingleShip(?, @err)");
        $stmt->execute([$shipCode]);
        $ship = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ship === false) {
            throw new \UnexpectedValueException("Failed to fetch ship '$shipCode'");
        }

        return new Event(['$schema' => 'https://dekimo.lto.network/ship', 'id' => $shipCode] + $ship);
    }

    /**
     * Create a new event for a ship.
     */
    public function createEvent(string $shipCode, DateTimeInterface $lastRequested): ?Event
    {
        $data = [
            'registration' => $this->fetchData('Registration', $shipCode, $lastRequested),
            'deviation' => $this->fetchData('Deviation', $shipCode, $lastRequested),
            'status_time' => $this->fetchData('ShipStatusTime', $shipCode, $lastRequested),
            'status_time_n2000' => $this->fetchData('N2000StatusTime', $shipCode, $lastRequested),
            'status_time_sea' => $this->fetchData('SeaStatusTime', $shipCode, $lastRequested),
            'status_time_fish_activity' => $this->fetchData('FishActivityStatusTime', $shipCode, $lastRequested),
        ];

        $history = [
            'registration' => $this->fetchData('RegistrationHistory', $shipCode, $lastRequested),
            'deviation' => $this->fetchData('DeviationHistory', $shipCode, $lastRequested),
            'status_time' => $this->fetchData('ShipStatusTimeHistory', $shipCode, $lastRequested),
            'status_time_n2000' => $this->fetchData('N2000StatusTimeHistory', $shipCode, $lastRequested),
            'status_time_sea' => $this->fetchData('SeaStatusTimeHistory', $shipCode, $lastRequested),
            'status_time_fish_activity' => $this->fetchData('FishActivityStatusTimeHistory', $shipCode, $lastRequested),
        ];

        $keys = array_keys($data);

        if ($data === array_fill_keys($keys, []) && $history === array_fill_keys($keys, [])) {
            return null;
        }

        return new Event([
            '$schema' => 'https://dekimo.lto.network/shipevent',
            'id' => $shipCode,
            'data' => $data,
            'history' => $history
        ]);
    }

    /**
     * Fetch ship data.
     */
    protected function fetchData(string $proc, string $shipCode, DateTimeInterface $lastRequested)
    {
        $stmt = $this->mysql->prepare("CALL IP_sel_Last{$proc}(?, ?, @err)");
        $stmt->execute([$shipCode, $lastRequested->format('Y-m-d\TH:i-s')]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
