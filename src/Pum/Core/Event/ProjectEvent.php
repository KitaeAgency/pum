<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\Project;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to project.
 *
 * @see Pum\Core\Events
 */
class ProjectEvent extends Event
{
    protected $project;
    protected $factory;

    public function __construct(Project $project, ObjectFactory $factory)
    {
        $this->project    = $project;
        $this->factory = $factory;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory()
    {
        return $this->factory;
    }
}
