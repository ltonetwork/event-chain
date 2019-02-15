<?php declare(strict_types=1);

/**
 * Class for type casting
 */
class TypeCast extends Jasny\TypeCast
{
    /**
     * Cast value to an object of a class
     *
     * @param string $class
     * @return object|mixed
     */
    public function toClass($class)
    {
        if ($this->getValue() instanceof Event && is_a($class, LTO\Event::class, true)) {
            return $this->toLtoEvent();
        }

        if ($this->getValue() instanceof LTO\EventChain && is_a($class, EventChain::class, true)) {
            return $this->toEventChain();
        }

        return parent::toClass($class);
    }

    /**
     * Cast Event to LTO\Event
     *
     * @return LTO\Event
     */
    protected function toLtoEvent(): LTO\Event
    {
        $values = $this->getValue()->getValues();
        $ltoEvent = new LTO\Event();

        foreach ($values as $key => $value) {
            $ltoEvent->$key = $value;
        }

        return $ltoEvent;
    }

    /**
     * Cast LTO\EventChain to EventChain
     *
     * @return EventChain
     */
    protected function toEventChain(): EventChain
    {
        $ltoChain = $this->getValue();

        $chain = new EventChain();
        $chain->id = $ltoChain->id;
        $chain->events = new Jasny\DB\EntitySet($ltoChain->events);

        return $chain;
    }
}
