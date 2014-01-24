<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to beam.
 *
 * @see Pum\Core\Events
 */
class ProjectBeamEvent extends Event
{
    protected $project;
    protected $beam;

    public function __construct(Project $project, Beam $beam, ObjectFactory $factory)
    {
        $this->project = $project;
        $this->beam    = $beam;
    }

    public function getBeam()
    {
        return $this->beam;
    }

    public function getProject()
    {
        return $this->beam;
    }
}
