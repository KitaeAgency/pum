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
        $entity->_pumRawSet($this->name, $value);
    }

    public function getValue($entity = null)
    {
        $entity->_pumRefreshField($this->name);
        return $entity->_pumRawGet($this->name);
    }
}
