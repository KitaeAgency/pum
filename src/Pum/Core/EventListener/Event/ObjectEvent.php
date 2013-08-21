<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Object\Object;
use Symfony\Component\EventDispatcher\Event;

class ObjectEvent extends Event
{
    protected $object;

    public function __construct(Object $object)
    {
        $this->object = $object;
    }

    /**
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }
}
