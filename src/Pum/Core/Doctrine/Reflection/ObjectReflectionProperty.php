<?php

namespace Pum\Core\Doctrine\Reflection;

class ObjectReflectionProperty
{
    public $name = null;
    public $class = null;

    public function __construct($class, $name)
    {
        $this->class = $class;
        $this->name = $name;
    }

    public function setAccessible($flag) {}

    public function setValue($entity = null, $value = null)
    {
        $entity->set($this->name, $value);
    }

    public function getValue($entity = null)
    {
        return $entity->get($this->name);
    }
}
