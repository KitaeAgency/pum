<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\Project;

abstract class AbstractBuildContext
{
    /**
     * @var ClassBuilder
     */
    protected $classBuilder;

    /**
     * @var Project
     */
    protected $project;

    public function __construct(Project $project, ClassBuilder $classBuilder)
    {
        $this->classBuilder = $classBuilder;
        $this->project      = $project;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @var ClassBuilder
     */
    public function getClassBuilder()
    {
        return $this->classBuilder;
    }
}
