<?php

declare(strict_types=1);

use Improved\Iterator\CombineIterator;

/**
 * Combine iterator that sets keys and values to null if one of them is missing.
 * @todo Should be the default. Backport to improved/iterable.
 */
class FillCombineIterator extends CombineIterator
{
    /**
     * Checks if the iterator is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->values->valid() || $this->keys->valid();
    }
}
