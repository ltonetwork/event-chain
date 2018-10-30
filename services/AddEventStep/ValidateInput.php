<?php declare(strict_types=1);

namespace AddEventStep;

use EventChain;
use Jasny\ValidationResult;

/**
 * Basic validation for submitted event chain. This includes the integrity of the submitted (partial) chain. More
 * extensive validation is going to be done event by event.
 */
class ValidateInput
{
    /**
     * @var EventChain
     */
    protected $chain;

    /**
     * Class constructor.
     *
     * @param EventChain $chain
     */
    public function __construct(\EventChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Invoke the step.
     *
     * @param EventChain       $newEvents
     * @param ValidationResult $validation
     * @return EventChain
     * @throws \UnexpectedValueException
     */
    public function __invoke(EventChain $newEvents, ValidationResult $validation): EventChain
    {
        if ($this->chain->id !== $newEvents->id) {
            throw new \UnexpectedValueException("Can't add events of a different chain");
        }

        $validation->add($newEvents->validate());

        return $newEvents;
    }
}
