<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\Beam;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to beam.
 *
 * @see Pum\Core\Events
 */
class BeamEvent extends Event
{
    protected $beam;

    public function __construct(Beam $beam)
    {
        $this->beam = $beam;
    }

    public function getBeam()
    {
        return $this->beam;
    }
}
