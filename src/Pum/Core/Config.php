<?php

namespace Pum\Core;

use Pum\Core\Driver\DriverInterface;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\Type\Factory\TypeFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    protected $cacheDir;
    protected $objectFactories = array();

    public function __construct(DriverInterface $driver, TypeFactoryInterface $typeFactory, $cacheDir, array $extensions = array(), EventDispatcherInterface $eventDispatcher = null)
    {
        if (null === $eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        $this->driver          = $driver;
        $this->eventDispatcher = $eventDispatcher;
        $this->typeFactory     = $typeFactory;
        $this->cacheDir        = $cacheDir;

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

    /**
     * @return TypeFactory
     */
    public function getTypeFactory()
    {
        return $this->typeFactory;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory($name)
    {
        if (isset($this->objectFactories[$name])) {
            return $this->objectFactories[$name];
        }

        return $this->objectFactories[$name] = new ObjectFactory($name, $this->cacheDir);
    }
}
