<?php

use Jasny\Router\ControllerFactory as Base;
use Jasny\Autowire\AutowireInterface;
use Psr\Container\ContainerInterface;

/**
 * Container aware controller factory
 */
class ControllerFactory extends Base
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Class constructor
     *
     * @param callable $chain  Instantiate method
     */
    public function __construct($chain = null, ContainerInterface $container = null)
    {
        if (isset($chain) && !is_callable($chain)) {
            throw new \InvalidArgumentException("Chain should be callable");
        }

        $this->chain = $chain;
        $this->container = $container;
    }

    /**
     * Instantiate a controller object
     *
     * @param string $class
     * @return callable|object
     */
    protected function instantiate($class)
    {
        /** @var AutowireInterface $autowire */
        $autowire = $this->container->get(AutowireInterface::class);

        return $autowire->instantiate($class);
    }
}
