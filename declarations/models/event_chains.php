<?php declare(strict_types=1);

return [
    'models.event_chains' => function () {
        return new EventChainGateway();
    }
];
