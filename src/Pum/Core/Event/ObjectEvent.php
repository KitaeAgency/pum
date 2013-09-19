<?php

namespace Pum\Core\Event;

use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to object.
 *
 * @see Pum\Core\Events
 */
class ObjectEvent extends Event
{
    protected $object;
    protected $factory;

    public function __construct($object, ObjectFactory $factory)
    {
        $this->object  = $object;
        $this->factory = $factory;
    }

    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory()
    {
        return $this->factory;
    }
}
