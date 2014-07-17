<?php

namespace Pum\Core\Schema;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

interface SchemaInterface
{
    /**
     * Returns all project names.
     *
     * @return array
     */
    public function getProjectNames();

    /**
     * Returns all beam names.
     *
     * @return array
     */
    public function getBeamNames();

    /**
     * Returns a given project.
     *
     * @return Project
     *
     * @throws Pum\Core\Exception\DefinitionNotFoundException
     */
    public function getProject($name);

    /**
     * Returns a given beam.
     *
     * @return Beam
     *
     * @throws Pum\Core\Exception\DefinitionNotFoundException
     */
    public function getBeam($name);

    /**
     * Check if a beam exist for à given name
     * @param $name
     * @return bool
     */
    public function hasBeam($name);

    /**
     * Deletes a beam.
     */
    public function deleteBeam(Beam $beam);

    /**
     * Saves a beam.
     */
    public function saveBeam(Beam $beam);

    /**
     * Deletes a project.
     */
    public function deleteProject(Project $project);

    /**
     * Saves a project.
     */
    public function saveProject(Project $project);
}
