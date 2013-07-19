<?php

namespace Pum\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;

/**
 * Main class for accessing and manipulating dynamic models.
 */
class SchemaManager
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(Config $config, LoggerInterface $logger = null)
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Saves a project (existing or new).
     */
    public function saveProject(Project $project)
    {
        $this->config->getDriver()->saveProject($project);
    }

    /**
     * Saves a beam (existing or new).
     */
    public function saveBeam(Beam $beam)
    {
        $this->config->getDriver()->saveBeam($beam);
    }

    /**
     * Deletes a project (existing or new).
     */
    public function deleteProject(Project $project)
    {
        $this->config->getDriver()->deleteProject($project);
    }

    /**
     * Deletes a beam (existing or new).
     */
    public function deleteBeam(Beam $beam)
    {
        $this->config->getDriver()->deleteBeam($beam);
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
        $this->logger->info(sprintf('Load object definition "%s" from project "%s"', $name, $projectName));

        $project = $this->config->getDriver()->getProject($projectName);

        return $project->getDefinition($name);
    }

    /**
     * @return Beam
     */
    public function getBeam($name)
    {
        $this->logger->info(sprintf('Load beam "%s"', $name));

        return $this->config->getDriver()->getBeam($name);
    }

    /**
     * @return Project
     */
    public function getProject($name)
    {
        $this->logger->info(sprintf('Load project "%s"', $name));

        return $this->config->getDriver()->getProject($name);
    }

    /**
     * Returns all beams.
     *
     * @return array
     */
    public function getAllBeams()
    {
        $this->logger->info(sprintf('Load all beams', $name));

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
        $this->logger->info('Load all projects');

        $result = array();
        foreach ($this->config->getDriver()->getAllProjectNames() as $name) {
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
        $this->logger->info(sprintf('Load type "%s".', $name));

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
}
