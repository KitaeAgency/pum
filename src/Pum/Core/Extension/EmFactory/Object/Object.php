<?php

namespace Pum\Core\Extension\EmFactory\Object;

/**
 * Base class from which all PUM objects extend.
 */
class Object
{
    private $data = array();

    public function get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }
}
