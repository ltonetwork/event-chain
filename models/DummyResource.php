<?php

declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\Entity\Dynamic;
use function Jasny\str_before;

/**
 * A dummy resource will be ignored.
 */
class DummyResource implements ResourceInterface
{
    use ResourceBase;
}
