<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\Beam;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to beam.
 *
 * @see Pum\Core\Events
 */
class BeamEvent extends Event
{
    protected $beam;
    protected $factory;

    public function __construct(Beam $beam, ObjectFactory $factory)
    {
        $this->beam    = $beam;
        $this->factory = $factory;
    }

    public function getBeam()
    {
        return $this->beam;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory()
    {
        return $this->factory;
    }
}
