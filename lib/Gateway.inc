<?php declare(strict_types=1);

use Jasny\DB\Entity;
use Jasny\DB\EntitySet;

/**
 * Basic data gateway.
 * @deprecated To be replaced with the new Jasny DB layer.
 */
interface Gateway
{
    /**
     * Create an event chain.
     *
     * @return Entity
     */
    public function create();

    /**
     * Fetch a document.
     *
     * @param string|array $id  ID or filter
     * @param array        $opts
     * @return Entity
     */
    public function fetch($id, array $opts = []);

    /**
     * Check if a document exists.
     *
     * @param string|array $id  ID or filter
     * @param array        $opts
     * @return boolean
     */
    public function exists($id, array $opts = []);

    /**
     * Fetch all documents.
     *
     * @param array     $filter
     * @param array     $sort
     * @param int|array $limit  Limit or [limit, offset]
     * @param array     $opts
     * @return EntitySet&iterable<Entity>
     */
    public function fetchAll(array $filter = [], $sort = [], $limit = null, array $opts = []);

    /**
     * Count all documents in the collection
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []);
}
