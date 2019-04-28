<?php declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\Configuration as Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\MongoDb as MongoDbDriver;
use Codeception\TestInterface;

/**
 * Works with MongoDb database.
 *
 * Added working with additional databases, supporting only setting connection and cleanup
 */
class MongoMultiDb extends \Codeception\Module\MongoDb
{
    /**
     * @var \Codeception\Lib\Driver\MongoDb[]
     */
    public $multiDrivers = [];

    public function _initialize()
    {
        try {
            $multidb = $this->config['multidb'] ?? [];

            foreach ($multidb as $config) {
                $this->multiDrivers[] = MongoDbDriver::create(
                    $config['dsn'],
                    $config['user'],
                    $config['password']
                );                
            }
        } catch (\MongoConnectionException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating Mongo connection');
        }

        parent::_initialize();
    }

    protected function cleanup()
    {
        foreach ($this->multiDrivers as $driver) {
            $dbh = $driver->getDbh();

            if (!$dbh) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "No connection to database. Remove this module from config if you don't need database repopulation"
                );
            }
            try {
                $driver->cleanup();
            } catch (\Exception $e) {
                throw new ModuleException(__CLASS__, $e->getMessage());
            }
        }

        parent::cleanup();
    }
}
