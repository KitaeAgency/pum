<?php

namespace Pum\Core\Event;

use Pum\Core\Definition\Project;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Object used for events.
 *
 * @see Pum\Core\Events
 */
class Event extends BaseEvent
{
    private $factory;

    public function setObjectFactory(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return ObjectFactory
     */
    public function getObjectFactory()
    {
        if (null === $this->factory) {
            throw new \RuntimeException('Factory is missing from event.');
        }

        return $this->factory;
    }
}
