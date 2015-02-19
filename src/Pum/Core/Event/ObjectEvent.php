<?php

namespace Pum\Core\Event;

use Pum\Core\ObjectFactory;

/**
 * Object used for events related to object.
 *
 * @see Pum\Core\Events
 */
class ObjectEvent extends Event
{
    protected $object;

    public function __construct($object, ObjectFactory $factory = null)
    {
        $this->object = $object;
        $this->setObjectFactory($factory);
    }

    public function getObject()
    {
        return $this->object;
    }
}
