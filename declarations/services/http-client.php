<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

return [
    ClientInterface::class => function () {
        return new Client(['timeout' => 5]);
    }
];
