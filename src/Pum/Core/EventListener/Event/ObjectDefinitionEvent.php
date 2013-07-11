<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to object definition.
 *
 * @see Pum\Core\Events
 */
class ObjectDefinitionEvent extends Event
{
    protected $definition;

    public function __construct(ObjectDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function getObjectDefinition()
    {
        return $this->definition;
    }
}
