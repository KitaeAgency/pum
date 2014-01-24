<?php

namespace Pum\Core\Event;

/**
 * Object used for events related to object.
 *
 * @see Pum\Core\Events
 */
class ObjectEvent extends Event
{
    protected $object;

    public function __construct($object)
    {
        $this->object  = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}
