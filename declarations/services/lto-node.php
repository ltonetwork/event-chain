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

        $accountSeed = getenv('LTO_ACCOUNT_SEED_BASE58');

        if ((string)$accountSeed === '' && $container->get(ApplicationEnv::class)->is('prod')) {
            throw new RuntimeException("LTO account seed missing; set LTO_ACCOUNT_SEED_BASE58 env var");
        }

        return (string)$accountSeed !== ''
            ? $factory->seed(base58_decode($accountSeed))
            : $factory->create(arrayify(get_object_vars((new Config)->load('config/node.yml'))));
    },
    'node.account' => function (ContainerInterface $container) {
        return $container->get(Account::class);
    },
];
