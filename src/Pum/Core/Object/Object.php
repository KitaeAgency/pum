<?php

namespace Pum\Core\Object;

use Pum\Core\Type\Factory\TypeFactoryInterface;

/**
 * Base class from which all PUM objects extend.
 */
abstract class Object
{
    /**
     * Raw values, internal for storage.
     *
     * @var array
     */
    private $_pumRawData = array();

    /**
     * Field values.
     *
     * @var array
     */
    private $_pumData = array();

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
     * We will always pretend to be aware of any field.
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return true;
    }

    /**
     * Magic method to read a value.
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_pumGet($name);
    }

    /**
     * Magic method to write a value.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->_pumSet($name, $value);
    }

    /**
     * Returns a high-level value (not a raw value).
     */
    public function _pumGet($name)
    {
        if (!$this->_pumHasField($name)) {
            return $this->_pumRawGet($name);
        }

        if (!array_key_exists($name, $this->_pumData)) {
            $this->_pumData[$name] = static::_pumGetMetadata()->readValue($this, $name);
        }

        return $this->_pumData[$name];
    }

    /**
     * Sets a high-level value (not a raw value).
     */
    public function _pumSet($name, $value)
    {
        if (!$this->_pumHasField($name)) {
            return $this->_pumRawSet($name, $value);
        }

        $this->_pumData[$name] = $value;
    }

    /**
     * This method should only be called by EM Factory.
     *
     * Don't do this at home.
     */
    public function _pumRawGet($name)
    {
        return isset($this->_pumRawData[$name]) ? $this->_pumRawData[$name] : null;
    }

    /**
     * This method should only be called by EM Factory.
     *
     * Don't do this at home.
     */
    public function _pumRawSet($name, $value)
    {
        $this->_pumRawData[$name] = $value;

        return $this;
    }

    /**
     * The actual "isset" method, only used internally.
     *
     * @return boolean
     */
    public function _pumHasField($name)
    {
        return static::_pumGetMetadata()->hasField($name);
    }

    /**
     * Used for collections.
     */
    public function add($name, $value)
    {
        $this->_pumRawData[$name][] = $value;
    }

    public function _pumRefreshField($name)
    {
        // not read, not modified, no need to refresh
        if (!isset($this->_pumData[$name])) {
            return;
        }

        static::_pumGetMetadata()->writeValue($this, $name, $this->_pumGet($name));
    }

    public function _pumIdentifier()
    {
        return static::_pumGetMetadata()->getIdentifier($this);
    }
}
