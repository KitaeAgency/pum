<?php

namespace Pum\Core;

use Pum\Core\Driver\DriverInterface;
use Pum\Core\Type\Factory\TypeFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Configuration of a schema manager.
 */
class Config
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var TypeFactoryInterface
     */
    protected $typeFactory;

    /**
     * Constructor.
     *
     * @param DriverInterface          $driver          driver to use to access definitions
     * @param EventDispatcherInterface $eventDispatcher dispatcher to use for events
     */
    public function __construct(DriverInterface $driver, TypeFactoryInterface $typeFactory, array $extensions = array(), EventDispatcherInterface $eventDispatcher = null)
    {
        if (null === $eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        $this->driver          = $driver;
        $this->eventDispatcher = $eventDispatcher;
        $this->typeFactory     = $typeFactory;

        foreach ($extensions as $extension) {
            $this->eventDispatcher->addSubscriber($extension);
        }
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
