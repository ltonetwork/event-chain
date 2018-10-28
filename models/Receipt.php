<?php declare(strict_types=1);

/**
 * Receipt.
 * Receipts aren't needed yet, as all public LTO nodes are history nodes.
 */
class Receipt extends MongoSubDocument
{
    /**
     * @var string
     */
    public $targetHash;
}
