<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use LTO\AccountFactory;
use Improved\IteratorPipeline\Pipeline;

return [
    AccountFactory::class => function (ContainerInterface $container) {
        $config = $container->get('config');

        return new AccountFactory(isset($config->network) ? $config->network : 'T');
    },
    'participants' => function (ContainerInterface $container) {
        $factory = $container->get(AccountFactory::class);

        return Pipeline::with($container->get('config.participants'))
            ->filter(fn($participant) => isset($participant->publickey) && $participant->publickey !== '')
            ->map(fn($participant) => [
                'account' => $factory->createPublic($participant->publickey),
                'node' => $participant->node,
            ])
            ->toArray();
    }
];
