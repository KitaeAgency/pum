<?php

namespace Pum\Core\Doctrine\Reflection;

class ObjectReflectionClass extends \ReflectionClass
{
    public function getProperty($name)
    {
        return new ObjectReflectionProperty($this->name, $name);
    }
}
