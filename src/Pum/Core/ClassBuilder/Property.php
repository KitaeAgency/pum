<?php

namespace Pum\Core\ClassBuilder;

class Property
{
    protected $name;
    protected $defaultValue;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
