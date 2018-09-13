<?php

use LTO\Account;

/**
 * Service to extract the resource from an event
 */
class EventFactory
{
    /**
     * @var Account
     */
    protected $node;
    
    
    /**
     * Class constructor
     * 
     * @param Account $nodeAccount
     */
    public function __construct(Account $nodeAccount)
    {
        $this->node = $nodeAccount;
    }
    
    
    /**
     * Create an error event
     * 
     * @param string|string[] $reason  The reason for failure
     * @param Event[]         $events  The events that failed and haven't been processed yet
     * @return Event
     */
    public function createErrorEvent($reason, $events)
    {
        $message = is_string($reason) ? [$reason] : $reason;
        $body = compact($message, $events);
        
        $event = new LTO\Event($body, $events[0]->previous);
        $event->signWith($this->node);
        
        return (new Event())->setValues($event);
    }
}
