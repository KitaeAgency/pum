<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\Project;

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
        $this->project    = $project;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
