<?php

/**
 * Receipt.
 * Receipts aren't needed yet, as all public LTO nodes are history nodes.
 */
class Receipt extends MongoSubDocument
{
    public $targetHash;
}
