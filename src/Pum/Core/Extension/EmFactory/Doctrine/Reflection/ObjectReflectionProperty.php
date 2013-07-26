<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Reflection;

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
        $entity->__pum__rawSet($this->name, $value);
    }

    public function getValue($entity = null)
    {
        return $entity->__pum__rawGet($this->name);
    }
}
