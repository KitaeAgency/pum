<?php

namespace Pum\Core\Definition;

use Symfony\Component\EventDispatcher\Event;

abstract class EventObject
{
    private $events = array();

    public function raise($eventName, Event $event)
    {
        if (!array_key_exists($eventName, $this->events)) {
            $this->events[$eventName] = array($eventName, $event);
        }
    }

    public function popEvents()
    {
        return $this->events;
    }
}
