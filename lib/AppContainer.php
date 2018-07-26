<?php

use Mouf\Picotainer\Picotainer;
use Psr\Container\ContainerInterface;
use Mouf\Picotainer\PicotainerNotFoundException;

/**
 * The Application container.
 * 
 * @see https://github.com/thecodingmachine/picotainer
 * @codeCoverageIgnore
 */
class AppContainer extends Picotainer
{
    /**
     * Class constructor
     * 
     * @param array              $entries
     * @param ContainerInterface $delegateLookupContainer
     */
    public function __construct(array $entries = [], ContainerInterface $delegateLookupContainer = null)
    {
        $entries += static::loadEntries('declarations/services') + static::loadEntries('declarations/models');

        parent::__construct($entries, $delegateLookupContainer);
    }

    /**
     * Get container entries
     *
     * @param string $path
     * @return callback[]
     */
    protected static function loadEntries(string $path): array
    {
        $sources = glob("$path/*.php");

        return array_reduce($sources, function(array $entries, string $source) {
            $newEntries = include $source;

            if (!is_array($newEntries)) {
                trigger_error("Failed to load entries from '$source'");
            }

            return $entries + $newEntries;
        }, []);
    }
}
