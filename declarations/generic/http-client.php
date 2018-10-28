<?php declare(strict_types=1);

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

return [
    ClientInterface::class => function () {
        return new Client(['timeout' => 20]);
    }
];
