<?php declare(strict_types=1);

use Jasny\ApplicationEnv;
use Jasny\Config;
use Psr\Container\ContainerInterface;
use LTO\AccountFactory;
use LTO\Account;
use function Jasny\arrayify;

return [
    Account::class => function (ContainerInterface $container) {
        /** @var AccountFactory $factory */
        $factory = $container->get(AccountFactory::class);
        $accountInfo = $container->get('config.lto.account');

        if ($accountInfo === '') {
            throw new RuntimeException("LTO account seed missing; set LTO_ACCOUNT_SEED_BASE58 env var");
        }

        return is_string($accountInfo)
            ? $factory->seed(base58_decode($accountInfo))
            : $factory->create(arrayify($accountInfo));
    },
    'node.account' => function (ContainerInterface $container) {
        return $container->get(Account::class);
    },
];
