<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

class ObjectBuildContext extends AbstractBuildContext
{
    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    public function __construct(Project $project, ClassBuilder $classBuilder, ObjectDefinition $object)
    {
        $this->objectDefinition = $object;
        
        parent::__construct($project, $classBuilder);
    }

    /**
     * @var ObjectDefinition
     */
    public function getObject()
    {
        return $this->objectDefinition;
    }
}
