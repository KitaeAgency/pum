<?php

namespace Pum\Core\Event;

use Pum\Core\ObjectFactory;

/**
 * Object used for events related to object.
 *
 * @see Pum\Core\Events
 */
class ObjectTreeEvent extends ObjectEvent
{
    protected $oldParent;
    protected $oldSequence;

    public function __construct($object, $oldParent, $oldSequence, ObjectFactory $factory = null)
    {
        parent::__construct($object, $factory);

        $this->oldParent = $oldParent;
        $this->oldSequence = $oldSequence;
    }

    public function getOldParent()
    {
        return $this->oldParent;
    }

    public function getOldSequence()
    {
        return $this->oldSequence;
    }
}
