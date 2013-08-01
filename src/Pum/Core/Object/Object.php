<?php

namespace Pum\Core\Object;

use Pum\Core\Type\Factory\TypeFactoryInterface;

/**
 * Base class from which all PUM objects extend.
 */
abstract class Object
{
    /**
     * Field values.
     *
     * @var array
     */
    private $__pum_data = array();

    /**
     * @var ObjectMetadata
     */
    private $__pum_metadata;

    public function __pum_setMetadata(ObjectMetadata $metadata)
    {
        return $this->__pum_metadata = $metadata;
    }

    /**
     * @return ObjectMetadata
     */
    public function __pum_getMetadata()
    {
        return $this->__pum_metadata;
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

    /**
     * We will always pretend to be aware of any field.
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return true;
    }

    /**
     * The actual "isset" method, only used internally.
     *
     * @return boolean
     */
    public function __pum__isset($name)
    {
        return $this->__pum_metadata->hasField($name);
    }

    /**
     * Magic method to read a value.
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->__pum__isset($name)) {
            return $this->__pum__rawGet($name);
        }

        return $this->__pum_metadata->readValue($this, $name);
    }

    /**
     * Magic method to write a value.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (!$this->__pum__isset($name)) {
            $this->__pum__rawSet($name, $value);

            return $this;
        }

        return $this->__pum_metadata->writeValue($this, $name, $value);
    }

    /**
     * For old-school people, not loving magic.
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * For old-school people, not loving magic.
     */
    public function set($name, $value)
    {
        $this->__set($name, $value);

        return $this;
    }

    /**
     * Used for collections.
     */
    public function add($name, $value)
    {
        $this->__pum_data[$name][] = $value;
    }
}
