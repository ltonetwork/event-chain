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
            $values = $this->getValue()->getValues();
            $ltoEvent = new LTO\Event();

            foreach ($values as $key => $value) {
                $ltoEvent->$key = $value;
            }

            return $ltoEvent;
        }

        return parent::toClass($class);        
    }
}
