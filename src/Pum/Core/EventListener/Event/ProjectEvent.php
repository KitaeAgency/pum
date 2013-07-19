<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\Project;
use Pum\Core\SchemaManager;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to project.
 *
 * @see Pum\Core\Events
 */
class ProjectEvent extends Event
{
    protected $project;
    protected $schemaManager;

    public function __construct(Project $project, SchemaManager $schemaManager)
    {
        $this->project    = $project;
        $this->schemaManager = $schemaManager;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getSchemaManager()
    {
        return $this->schemaManager;
    }
}
