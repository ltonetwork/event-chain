<?php declare(strict_types=1);

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
     * Get the account of the node.
     *
     * @return Account
     */
    public function getNodeAccount(): Account
    {
        return $this->node;
    }
    
    /**
     * Create an error event
     * 
     * @param string|string[] $reason  The reason for failure
     * @param Event[]         $events  The events that failed and haven't been processed yet
     * @return Event
     */
    public function createErrorEvent($reason, array $events): Event
    {
        $message = is_string($reason) ? [$reason] : $reason;
        $body = compact($message, $events);
        
        $previous = $events !== [] ? $events[0]->previous : null;
        $event = new LTO\Event($body, $previous);
        $event->signWith($this->node);
        
        return (new Event)->setValues($event);
    }
}
