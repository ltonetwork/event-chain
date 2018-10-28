<?php

/**
 * Mocking DI while using static methods.
 * This will be fixed with the new Jasny DB abstraction layer.
 */

use Jasny\DB\EntitySet;

return [
    'models.event_chains' => function () {
        return new class() implements Gateway {
            /**
             * Create an event chain.
             *
             * @return EventChain
             */
            public function create(): EventChain
            {
                return EventChain::create();
            }

            /**
             * Fetch an event chain.
             *
             * @param string|array $id  ID or filter
             * @param array        $opts
             * @return EventChain
             */
            public function fetch($id, array $opts = []): EventChain
            {
                return EventChain::fetch($id, $opts);
            }

            /**
             * Check if an event chain exists.
             *
             * @param string|array $id  ID or filter
             * @param array        $opts
             * @return bool
             */
            public function exists($id, array $opts = []): bool
            {
                return EventChain::exists($id, $opts);
            }

            /**
             * Fetch all event chains.
             *
             * @param array     $filter
             * @param array     $sort
             * @param int|array $limit  Limit or [limit, offset]
             * @param array     $opts
             * @return EntitySet&iterable<EventChain>
             */
            public function fetchAll(array $filter = [], $sort = [], $limit = null, array $opts = []): EntitySet
            {
                return EventChain::fetchAll($filter, $sort, $limit, $opts);
            }

            /**
             * Count all event chains in the collection
             *
             * @param array $filter
             * @param array $opts
             * @return int
             */
            public function count(array $filter = [], array $opts = []): int
            {
                return EventChain::count($filter, $opts);
            }
        };
    }
];
