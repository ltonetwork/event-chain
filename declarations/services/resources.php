<?php declare(strict_types=1);

use Improved as i;
use GuzzleHttp\ClientInterface;
use Jasny\Container\AutowireContainerInterface;
use Jasny\DB\ConfigurationException;
use LTO\Account;

return [
    ResourceFactory::class => function () {
        return new ResourceFactory();
    },
    ResourceStorage::class => function (AutowireContainerInterface $container) {
        $endpoints = (array)$container->get('config.endpoints');

        return $container->autowire(ResourceStorage::class, $endpoints);
    },
    ResourceTrigger::class => function (AutowireContainerInterface $container) {
        $triggers = (array)$container->get('config.triggers');

        return $container->autowire(ResourceTrigger::class, $triggers);
    },

    // Temporary service for resetting the workflow
    WorkflowReset::class => function (AutowireContainerInterface $container) {
        $enabled = $container->get('config.allow_full_reset') ?? false;

        $schema = 'https://specs.livecontracts.io/v0.2.0/process/schema.json#';
        $endpoint = i\iterable_find((array)$container->get('config.endpoints'), function ($endpoint) use ($schema) {
            return $endpoint->schema === $schema;
        });

        if ($endpoint === null) {
            throw new ConfigurationException("Missing endpoint for schema '{$schema}'");
        }

        $httpClient = $container->get(ClientInterface::class);
        $node = $container->get(Account::class);

        return new WorkflowReset($enabled, $endpoint->url, $httpClient, $node);
    },
];
