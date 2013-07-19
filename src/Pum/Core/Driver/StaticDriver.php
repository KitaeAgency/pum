<?php

namespace Pum\Core\Driver;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\Core\Exception\ProjectNotFoundException;

/**
 * Implementation of driver using a PHP array (no persistence).
 */
class StaticDriver implements DriverInterface
{
    protected $projects = array();
    protected $beams    = array();

    /**
     * {@inheritdoc}
     */
    public function getProjectNames()
    {
        return array_keys($this->projects);
    }

    /**
     * {@inheritdoc}
     */
    public function getBeamNames()
    {
        return array_keys($this->beams);
    }

    /**
     * {@inheritdoc}
     */
    public function getProject($name)
    {
        if (!isset($this->projects[$name])) {
            throw new ProjectNotFoundException($name);
        }

        return $this->projects[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getBeam($name)
    {
        if (!isset($this->beams[$name])) {
            throw new BeamNotFoundException($name);
        }

        return $this->beams[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBeam(Beam $beam)
    {
        unset($this->beams[$beam->getName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function saveBeam(Beam $beam)
    {
        $this->beams[$beam->getName()] = $beam;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProject(Project $project)
    {
        unset($this->beams[$project->getName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function saveProject(Project $project)
    {
        $this->projects[$project->getName()] = $project;
    }
}
