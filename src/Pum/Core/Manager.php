<?php

namespace Pum\Core;

use Doctrine\DBAL\Connection;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Doctrine\EntityManagerFactory;
use Pum\Core\Driver\DriverInterface;
use Pum\Core\EventListener\Event\ObjectDefinitionEvent;
use Pum\Core\Generator\ClassGenerator;
use Pum\Core\Object\Object;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Main class for accessing and manipulating dynamic entity managers.
 */
class Manager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var EntityManagerFactory
     */
    protected $factory;

    /**
     * @var ClassGenerator
     */
    protected $classGenerator;

    /**
     * @var array
     */
    protected $classMap = array();

    /**
     * Constructor.
     *
     * @param DriverInterface          $driver          driver to use to access definitions
     * @param EventDispatcherInterface $eventDispatcher dispatcher to use for events
     */
    public function __construct(DriverInterface $driver, Connection $connection, $cacheDir = null, EventDispatcherInterface $eventDispatcher = null)
    {
        if (null === $eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        $this->driver          = $driver;
        $this->eventDispatcher = $eventDispatcher;
        $this->classGenerator  = new ClassGenerator($cacheDir);
        $this->factory         = new EntityManagerFactory($this, $connection);
    }

    /**
     * Creates a new instance of a given object.
     *
     * @param string $type type of object
     *
     * @return Object
     */
    public function createObject($type)
    {
        $class = $this->prepare($type);

        $class = $this->factory
            ->getEntityManager()
            ->getRepository($class)
            ->getClassName()
        ;

        return new $class();
    }

    public function persist(Object $object)
    {
        $this->factory->getEntityManager()->persist($object);
    }

    public function flush(Object $object = null)
    {
        $this->factory->getEntityManager()->flush($object);
    }

    /**
     * Returns definition of an object.
     *
     * @param string $name name of the definition to fetch
     *
     * @return ObjectDefinition
     */
    public function getDefinition($name)
    {
        return $this->driver->getDefinition($name);
    }

    /**
     * Returns a definition from a given classname.
     *
     * @param string $class class name to reverse.
     *
     * @return ObjectDefinition
     */
    public function getDefinitionFromClassName($class)
    {
        if (!isset($this->classMap[$class])) {
            throw new \InvalidArgumentException('Never heard of '.$class);
        }

        return $this->getDefinition($this->classMap[$class]);
    }

    /**
     * Saves an object definition (existing or new).
     */
    public function saveDefinition(ObjectDefinition $definition)
    {
        $event = new ObjectDefinitionEvent($definition);

        $this->driver->save($definition);
        $this->eventDispatcher->dispatch(Events::OBJECT_DEFINITION_SAVE, $event);
    }


    /**
     * Deletes an object definition (existing or new).
     */
    public function deleteDefinition(ObjectDefinition $definition)
    {
        $event = new ObjectDefinitionEvent($definition);

        $this->driver->delete($definition);
        $this->eventDispatcher->dispatch(Events::OBJECT_DEFINITION_DELETE, $event);
    }

    /**
     * Returns all definition names.
     *
     * @return array an array of string (blog, post, ...)
     */
    public function getAllDefinitionNames()
    {
        return $this->driver->getAllDefinitionNames();
    }

    /**
     * @return string classname prepared for this type
     */
    private function prepare($type)
    {
        if (false === $class = $this->classGenerator->isGenerated($type)) {
            $class = $this->classGenerator->generate($this->getDefinition($type));
        }

        $this->classMap[$class] = $type;

        return $class;
    }
}
