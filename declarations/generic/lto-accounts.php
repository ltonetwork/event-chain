<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use LTO\AccountFactory;

return [
    AccountFactory::class => function (ContainerInterface $container) {
        $config = $container->get('config');

        return new AccountFactory(isset($config->network) ? $config->network : 'T');
    }
];
