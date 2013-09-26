<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\Project;

abstract class AbstractBuildContext extends AbstractContext
{
    /**
     * @var ClassBuilder
     */
    protected $classBuilder;

    public function __construct(Project $project, ClassBuilder $classBuilder)
    {
        $this->classBuilder = $classBuilder;

        parent::__construct($project);
    }

    /**
     * @var ClassBuilder
     */
    public function getClassBuilder()
    {
        return $this->classBuilder;
    }
}
