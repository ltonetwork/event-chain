<?php

use Improved as i;
use function Jasny\object_get_properties;

/**
 * Check if link to schema specification is valid
 * @param  string  $link
 * @param  string  $type
 * @return boolean
 */
function is_schema_link_valid(string $link, string $type)
{
    $pattern = '|https://specs\.livecontracts\.io/v\d+\.\d+\.\d+/' . preg_quote($type) . '/schema\.json#|';

    return (bool)preg_match($pattern, $link);
}

function debug_events($data, $hashOnly = false)
{
    $events = $data->events ?? $data;
    foreach ($events as $event) {
        $event = clone $event;

        if ($hashOnly) {
            error_log('HASH: ' . $event->hash);
        } else {
            $event->body = json_decode(base58_decode($event->body), true);

            if (preg_match('~/(scenario|process)/~', $event->body['$schema'])) {
                $event->body = [
                    '$schema' => $event->body['$schema'],
                    'rest' => 'is skipped' 
                ];            
            }

            error_log(var_export($event, true));            
        }
    }
}
