<?php

namespace Pum\Core\Definition;

use Symfony\Component\EventDispatcher\Event;

abstract class EventObject
{
    private $events = array();
    private $onceEvents = array();

    public function raise($eventName, Event $event)
    {
        $this->events[] = array($eventName, $event);
    }

    public function raiseOnce($eventName, Event $event)
    {
        if (isset($this->onceEvents[$eventName])) {
            return;
        }

        $this->onceEvents[$eventName] = array($eventName, $event);
    }

    public function popEvents()
    {
        $res = array_merge($this->events, $this->onceEvents);

        $this->events = array();
        $this->onceEvents = array();

        return $res;
    }
}
