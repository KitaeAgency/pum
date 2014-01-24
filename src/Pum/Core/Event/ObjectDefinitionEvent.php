<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\ObjectDefinition;

/**
 * Object used for events related to objectDefinition.
 *
 * @see Pum\Core\Events
 */
class ObjectDefinitionEvent extends Event
{
    protected $objectDefinition;

    public function __construct(ObjectDefinition $objectDefinition)
    {
        $this->objectDefinition = $objectDefinition;
    }

    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }
}
