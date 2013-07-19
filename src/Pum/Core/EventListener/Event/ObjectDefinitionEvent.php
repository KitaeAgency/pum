<?php

namespace Pum\Core\EventListener\Event;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Manager;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to object definition.
 *
 * @see Pum\Core\Events
 */
class ObjectDefinitionEvent extends Event
{
    protected $definition;
    protected $manager;

    public function __construct(ObjectDefinition $definition, Manager $manager)
    {
        $this->definition    = $definition;
        $this->manager = $manager;
    }

    public function getObjectDefinition()
    {
        return $this->definition;
    }

    public function getManager()
    {
        return $this->manager;
    }
}
