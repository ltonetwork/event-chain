<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Jasny\HttpSignature\HttpSignature;
use Jasny\HttpSignature\ClientMiddleware;
use LTO\Account;
use LTO\AccountFactory;
use LTO\Account\SignCallback;
use LTO\Account\VerifyCallback;

return [
    HttpSignature::class => function (ContainerInterface $container) {
        $node = $container->get(Account::class);
        $factory = $container->get(AccountFactory::class);

        $service = new HttpSignature(
            ['ed25519', 'ed25519-sha256'],
            new SignCallback($node),
            new VerifyCallback($factory)
        );

        $requiredReadHeaders = ['(request-target)', 'date', 'x-original-key-id'];
        $requiredWriteHeaders = array_merge($requiredReadHeaders, ['content-type', 'digest']);

        return $service
            ->withRequiredHeaders('default', $requiredReadHeaders)
            ->withRequiredHeaders('POST', $requiredWriteHeaders)
            ->withRequiredHeaders('PUT', $requiredWriteHeaders);
    },
    ClientMiddleware::class => function(ContainerInterface $container) {
        $node = $container->get(Account::class);
        $service = $container->get(HttpSignature::class);

        return new ClientMiddleware($service->withAlgorithm('ed25519'), $node->getPublicSignKey());
    }
];
