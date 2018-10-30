<?php declare(strict_types=1);

/**
 * @internal The new Jasny DB layer will not use the global scope.
 */

use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $db = $container->get('config.db');

    DB::resetGlobalState();
    DB::configure($db);
};
