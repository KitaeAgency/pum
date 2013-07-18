<?php

namespace Pum\Core;

use Doctrine\DBAL\Connection;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Type\Factory\TypeFactoryInterface;
use Pum\Core\Doctrine\EntityManagerFactory;
use Pum\Core\Driver\DriverInterface;
use Pum\Core\EventListener\Event\ObjectDefinitionEvent;
use Pum\Core\EventListener\SchemaListener;
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
    protected $emFactory;

    /**
     * @var TypeFactoryInterface
     */
    protected $typeFactory;

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
    public function __construct(DriverInterface $driver, Connection $connection, TypeFactoryInterface $typeFactory, $cacheDir = null, EventDispatcherInterface $eventDispatcher = null)
    {
        if (null === $eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        $this->driver          = $driver;
        $this->eventDispatcher = $eventDispatcher;
        $this->classGenerator  = new ClassGenerator($cacheDir);
        $this->emFactory       = new EntityManagerFactory($this, $connection);
        $this->typeFactory     = $typeFactory;

        $eventDispatcher->addSubscriber(new SchemaListener());
    }

    /**
     * @return EntityManager
     */
    public function getRepository($name)
    {
        return $this->emFactory->getEntityManager()->getRepository($this->prepare($name));
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

        $class = $this->emFactory
            ->getEntityManager()
            ->getRepository($class)
            ->getClassName()
        ;

        return new $class();
    }

    public function persist(Object $object)
    {
        $this->emFactory->getEntityManager()->persist($object);
    }

    public function flush(Object $object = null)
    {
        $this->emFactory->getEntityManager()->flush($object);
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
        $event = $this->createEvent($definition);

        $this->driver->save($definition);
        $this->eventDispatcher->dispatch(Events::OBJECT_DEFINITION_SAVE, $event);
    }


    /**
     * Deletes an object definition (existing or new).
     */
    public function deleteDefinition(ObjectDefinition $definition)
    {
        $event = $this->createEvent($definition);

        $this->eventDispatcher->dispatch(Events::OBJECT_DEFINITION_DELETE, $event);
        $this->driver->delete($definition);
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
     * Returns a field type.
     *
     * @return TypeInterface
     *
     * @throws Pum\Core\Exception\TypeNotFoundException
     */
    public function getType($name)
    {
        return $this->typeFactory->getType($name);
    }

    /**
     * Tests if manager is aware of a given field type.
     *
     * @return boolean
     */
    public function hasType($name)
    {
        return $this->typeFactory->getType($name);
    }

    /**
     * Prepares the generated class for a given type.
     *
     * @param string $type dynamic object name
     *
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

    private function createEvent(ObjectDefinition $definition)
    {
        $em = $this->emFactory->getEntityManager();
        $className = $this->classGenerator->generate($definition);
        $this->classMap[$className] = $definition->getName();

        return new ObjectDefinitionEvent($definition, $em, $className);
    }
}
