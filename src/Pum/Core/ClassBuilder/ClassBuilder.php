<?php

namespace Pum\Core\ClassBuilder;

class ClassBuilder
{
    protected $className;
    protected $implements = array();
    protected $extends;
    protected $methods;
    protected $properties;

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    public function setExtends($className)
    {
        $this->extends = $extends;

        return $this;
    }

    public function getExtends()
    {
        return $this->extends;
    }

    public function addProperty(Property $property){}
    public function createProperty($name, $visibility = Property::VISIBILITY_PROTECTED, $default = null){}
    public function addMethod($name, $signature, $body){}
    public function addConstant($name, $value){}
}
