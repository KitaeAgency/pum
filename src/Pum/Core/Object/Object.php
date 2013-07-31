<?php

namespace Pum\Core\Object;

use Pum\Core\Type\Factory\TypeFactoryInterface;

/**
 * Base class from which all PUM objects extend.
 */
class Object
{
    private $__pum_data = array();
    private $__pum_dataTypes = array();

    /**
     * This method should only be called by EM Factory.
     *
     * Don't do this at home.
     */
    protected function __pum__setTypeInstances(array $dataTypes)
    {
        $this->__pum_dataTypes = $dataTypes;
    }

    /**
     * This method should only be called by EM Factory.
     *
     * Don't do this at home.
     */
    public function __pum__rawGet($name)
    {
        return isset($this->__pum_data[$name]) ? $this->__pum_data[$name] : null;
    }

    /**
     * This method should only be called by EM Factory.
     *
     * Don't do this at home.
     */
    public function __pum__rawSet($name, $value)
    {
        $this->__pum_data[$name] = $value;

        return $this;
    }

    public function __isset($name)
    {
        return true;
    }

    public function __pum__isset($name)
    {
        return array_key_exists($name, $this->__pum_dataTypes);
    }

    public function __get($name)
    {
        if (!$this->__pum__isset($name)) {
            return $this->__pum__rawGet($name);
        }

        return $this->__pum_dataTypes[$name]->readValue($this, $name);
    }

    public function __set($name, $value)
    {
        if (!$this->__pum__isset($name)) {
            $this->__pum__rawSet($name, $value);

            return $this;
        }

        return $this->__pum_dataTypes[$name]->writeValue($this, $name, $value);
    }

    public function get($name)
    {
        return $this->__get($name);
    }

    public function set($name, $value)
    {
        $this->__set($name, $value);

        return $this;
    }

    public function add($name, $value)
    {
        $this->__pum_data[$name][] = $value;
    }
}
