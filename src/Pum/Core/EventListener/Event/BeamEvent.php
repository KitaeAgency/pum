<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\Beam;
use Pum\Core\SchemaManager;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to beam.
 *
 * @see Pum\Core\Events
 */
class BeamEvent extends Event
{
    protected $beam;
    protected $schemaManager;

    public function __construct(Beam $beam, SchemaManager $schemaManager)
    {
        $this->beam    = $beam;
        $this->schemaManager = $schemaManager;
    }

    public function getBeam()
    {
        return $this->beam;
    }

    public function getSchemaManager()
    {
        return $this->schemaManager;
    }
}
