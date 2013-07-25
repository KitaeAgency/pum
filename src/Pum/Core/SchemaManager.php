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
use Pum\Core\Type\Factory\TypeFactoryInterface;

/**
 * Main class for accessing and manipulating dynamic models.
 */
class SchemaManager
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Adds an extension to the configuration.
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->config->getEventDispatcher()->addSubscriber($extension);
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
        $this->config->getDriver()->saveProject($project);
        $this->config->getEventDispatcher()->dispatch(Events::PROJECT_CHANGE, new ProjectEvent($project, $this));
    }

    /**
     * Saves a beam (existing or new).
     */
    public function saveBeam(Beam $beam)
    {
        $this->config->getDriver()->saveBeam($beam);
        $this->config->getEventDispatcher()->dispatch(Events::BEAM_CHANGE, new BeamEvent($beam, $this));
    }

    /**
     * Deletes a project (existing or new).
     */
    public function deleteProject(Project $project)
    {
        $this->config->getEventDispatcher()->dispatch(Events::PROJECT_DELETE, new ProjectEvent($project, $this));
        $this->config->getDriver()->deleteProject($project);
    }

    /**
     * Deletes a beam (existing or new).
     */
    public function deleteBeam(Beam $beam)
    {
        $this->config->getEventDispatcher()->dispatch(Events::BEAM_DELETE, new BeamEvent($beam, $this));
        $this->config->getDriver()->deleteBeam($beam);
    }

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
        $project = $this->config->getDriver()->getProject($projectName);

        return $project->getDefinition($name);
    }

    /**
     * @return Beam
     */
    public function getBeam($name)
    {
        return $this->config->getDriver()->getBeam($name);
    }

    /**
     * @return Project
     */
    public function getProject($name)
    {
        return $this->config->getDriver()->getProject($name);
    }

    /**
     * Returns all beams.
     *
     * @return array
     */
    public function getAllBeams()
    {
        $result = array();
        foreach ($this->config->getDriver()->getBeamNames() as $name) {
            $result[] = $this->config->getDriver()->getBeam($name);
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
        foreach ($this->config->getDriver()->getProjectNames() as $name) {
            $result[] = $this->config->getDriver()->getProject($name);
        }

        return $result;
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
        return $this->config->getTypeFactory()->getType($name);
    }

    /**
     * Tests if manager is aware of a given field type.
     *
     * @return boolean
     */
    public function hasType($name)
    {
        return $this->config->getTypeFactory()->getType($name);
    }

    /**
     * @return array an array of string
     */
    public function getTypeNames()
    {
        return $this->config->getTypeFactory()->getTypeNames();
    }

    /**
     * Creates a schema manager from a driver and a type factory.
     *
     * @return SchemaManager
     */
    static public function create(DriverInterface $driver, TypeFactoryInterface $factory)
    {
        $config = new Config($driver, $factory);

        return new self($config);
    }
}
