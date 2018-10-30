<?php declare(strict_types=1);

/**
 * @internal The new Jasny DB layer will not use the global scope.
 */

use Psr\Container\ContainerInterface;
use Jasy\DB;

return function (ContainerInterface $container) {
    DB::configure($container->get('config.db'));
};
