<?php

declare(strict_types=1);

use Jasny\Persist\SQL\Query;
use function Jasny\array_without;

/**
 * Class BlackBoxStore
 */
class ShipStore
{
    protected PDO $mysql;

    public function __construct(PDO $mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * Add a new ship to the DB.
     */
    public function storeShip(array $info): void
    {
        $data = array_without($info, ['$schema', 'id']);
        $this->insert('Ship', array_keys($data), $data);
    }

    /**
     * Store data from a new event.
     */
    public function storeShipEvent(array $info): void
    {
        $this->insertData('ProcessedRegistration', $info['data']['registration'] ?? []);
        $this->insertData('ProcRegDev', $info['data']['deviation'] ?? []);
        $this->insertData('StatusTime', $info['data']['status_time'] ?? []);
        $this->insertData('StatusTime_N200', $info['data']['status_time_n200'] ?? []);
        $this->insertData('StatusTime_Sea', $info['data']['status_time_sea'] ?? []);
        $this->insertData('StatusTime_FishActivity', $info['data']['status_time_fish_activity'] ?? []);

        $this->insertData('HistProcReg', $info['history']['registration'] ?? []);
        $this->insertData('HistProcRegDev', $info['history']['deviation'] ?? []);
        $this->insertData('HistStatusTime', $info['history']['status_time'] ?? []);
        $this->insertData('HistStatusTime_N200', $info['history']['status_time_n200'] ?? []);
        $this->insertData('HistStatusTime_Sea', $info['history']['status_time_sea'] ?? []);
        $this->insertData('HistStatusTime_FishActivity', $info['history']['status_time_fish_activity'] ?? []);
    }

    /**
     * Insert event data.
     */
    protected function insertData(string $table, array $data): void
    {
        if ($data !== []) {
            $this->insert($table, array_keys($data[0]), ...$data);
        }
    }

    /**
     * Insert data into a table
     *
     * @param string $table
     * @param array $columns
     * @param array ...$rows
     */
    protected function insert(string $table, array $columns, array ...$rows): void
    {
        $query = Query::build('mysql')->insert()->into("T_{$table}")->columns($columns);
        $query->onDuplicateKeyUpdate($columns[0]);
        $defaults = array_fill_keys($columns, null);

        foreach ($rows as $row) {
            $values = array_values(array_intersect_key(array_merge($defaults, $row), $defaults));
            $query->values($values);
        }

        $this->mysql->query((string)$query);
    }
}
