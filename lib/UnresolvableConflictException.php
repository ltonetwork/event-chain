<?php declare(strict_types=1);

/**
 * A fork has been detected, but it can't be (automatically) resolved.
 */
class UnresolvableConflictException extends RuntimeException
{
    /**
     * Both events haven't been anchored yet.
     */
    public const NOT_ANCHORED = 1;
}
