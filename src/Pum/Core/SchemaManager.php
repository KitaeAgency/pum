<?php

namespace Pum\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\DriverInterface;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Extension\ExtensionInterface;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\Type\Factory\TypeFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Main class for accessing and manipulating dynamic models.
 */
class SchemaManager
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var TypeFactoryInterface
     */
    protected $typeFactory;

    /**
     * Directory to cache objects.
     */
    protected $cacheDir;

    /**
     * @var array static cache of object factories.
     */
    protected $objectFactories = array();

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(DriverInterface $driver, TypeFactoryInterface $typeFactory, $cacheDir, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->driver          = $driver;
        $this->typeFactory     = $typeFactory;
        $this->cacheDir        = $cacheDir;
        $this->eventDispatcher = null === $eventDispatcher ? new EventDispatcher() : $eventDispatcher;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory($projectName)
    {
        if (isset($this->objectFactories[$projectName])) {
            return $this->objectFactories[$projectName];
        }

        return $this->objectFactories[$projectName] = new ObjectFactory($this, $projectName, $this->cacheDir.'/'.$projectName);
    }

    /**
     * Adds an extension to the schema manager.
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->eventDispatcher->addSubscriber($extension);
        $this->extensions[$extension->getName()] = $extension;
        $extension->setSchemaManager($this);

        return $this;
    }

    public function getExtension($name)
    {
        if (!isset($this->extensions[$name])) {
            throw new ExtensionNotFoundException($name, array_keys($this->extensions));
        }

        return $this->extensions[$name];
    }

    /**
     * Saves a project (existing or new).
     */
    public function saveProject(Project $project)
    {
        $this->driver->saveProject($project);

        $this->getObjectFactory($project->getName())->clearCache();
        $this->eventDispatcher->dispatch(Events::PROJECT_CHANGE, new ProjectEvent($project, $this));
    }

    /**
     * Saves a beam (existing or new).
     */
    public function saveBeam(Beam $beam)
    {
        $this->driver->saveBeam($beam);

        foreach ($this->getProjectsUsingBeam($beam) as $project) {
            $this->getObjectFactory($project->getName())->clearCache();
        }

        $this->eventDispatcher->dispatch(Events::BEAM_CHANGE, new BeamEvent($beam, $this));
    }

    /**
     * Deletes a project (existing or new).
     */
    public function deleteProject(Project $project)
    {
        $this->getObjectFactory($project->getName())->clearCache();

        $this->eventDispatcher->dispatch(Events::PROJECT_DELETE, new ProjectEvent($project, $this));
        $this->driver->deleteProject($project);
    }

    /**
     * Deletes a beam (existing or new).
     */
    public function deleteBeam(Beam $beam)
    {
        foreach ($this->getProjectsUsingBeam($beam) as $project) {
            $this->getObjectFactory($project->getName())->clearCache();
        }

        $this->eventDispatcher->dispatch(Events::BEAM_DELETE, new BeamEvent($beam, $this));

        $this->driver->deleteBeam($beam);
    }

    /**
     * @return array
     */
    public function getProjectsUsingBeam(Beam $beam)
    {
        $result = array();

        foreach ($this->getAllProjects() as $project) {
            if ($project->hasBeam($beam)) {
                $result[] = $project;
            }
        }

        return $result;
    }

    /**
     * Returns definition of an object.
     *
     * @param string $name name of the definition to fetch
     *
     * @return ObjectDefinition
     */
    public function getDefinition($projectName, $name)
    {
        $project = $this->driver->getProject($projectName);

        return $project->getObject($name);
    }

    /**
     * @return Beam
     */
    public function getBeam($name)
    {
        return $this->driver->getBeam($name);
    }

    /**
     * @return Project
     */
    public function getProject($name)
    {
        return $this->driver->getProject($name);
    }

    /**
     * Returns all beams.
     *
     * @return array
     */
    public function getAllBeams()
    {
        $result = array();
        foreach ($this->driver->getBeamNames() as $name) {
            $result[] = $this->driver->getBeam($name);
        }

        return $result;
    }

    /**
     * Returns all projects.
     *
     * @return array
     */
    public function getAllProjects()
    {
        $result = array();
        foreach ($this->driver->getProjectNames() as $name) {
            $result[] = $this->driver->getProject($name);
        }

        return $result;
    }

    /**
     * @return TypeFactoryInterface
     */
    public function getTypeFactory()
    {
        return $this->typeFactory;
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
     * @return array an array of string
     */
    public function getTypeNames()
    {
        return $this->typeFactory->getTypeNames();
    }
}
