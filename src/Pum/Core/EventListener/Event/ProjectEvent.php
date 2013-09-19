<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\Project;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to project.
 *
 * @see Pum\Core\Events
 */
class ProjectEvent extends Event
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project       = $project;
    }

    public function getProject()
    {
        return $this->project;
    }
}
