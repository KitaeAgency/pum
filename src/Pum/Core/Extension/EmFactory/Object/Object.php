<?php

namespace Pum\Core\Extension\EmFactory\Object;

/**
 * Base class from which all PUM objects extend.
 */
class Object
{
    private $data = array();

    public function __isset($name)
    {
        return true;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function add($name, $value)
    {
        $this->data[$name][] = $value;
    }
}
