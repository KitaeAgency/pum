<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\Project;
use Pum\Core\ObjectFactory;

abstract class AbstractContext
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

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

    public function setObjectFactory(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;

        return $this;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory()
    {
        if (null === $this->objectFactory) {
            throw new \RuntimeException('Missing object factory from context.');
        }

        return $this->objectFactory;
    }
}
