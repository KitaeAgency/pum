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
    protected $oldTreeSequence;

    public function __construct($object, $oldParent, $oldTreeSequence, ObjectFactory $factory = null)
    {
        parent::__construct($object, $factory);

        $this->oldParent = $oldParent;
        $this->oldTreeSequence = $oldTreeSequence;
    }

    public function getOldParent()
    {
        return $this->oldParent;
    }

    public function getOldTreeSequence()
    {
        return $this->oldTreeSequence;
    }
}
