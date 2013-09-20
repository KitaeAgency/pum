<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\Project;

abstract class AbstractContext
{
    /**
     * @var Project
     */
    protected $project;

    public function __construct(Project $project)
    {
        $this->project      = $project;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
