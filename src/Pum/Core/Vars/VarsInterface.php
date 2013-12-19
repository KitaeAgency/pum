<?php

namespace Pum\Core\Vars;

/**
 * Interface for vars.
 */
interface VarsInterface
{
    /**
    * Fetch a configuration value.
    *
    * @param string $key Key value
    * @param mixed $default Value to return if nothing is found in configuration
    *
    * @return mixed Value if exists, $default otherwise
    */
    public function get($key, $default = null);

    /**
    * Set a configuration value.
    *
    * @param string $key Key of the value to set
    * @param mixed $value Value to set
    */
    public function set($key, $value);

    /**
    * Removes a given value from config.
    *
    * @param string $value The key of value to remove.
    */
    public function remove($key);

    /**
    * Clear the config cache.
    *
    * @param boolean $result The result of clearing cache.
    */
    public function clear();

    /**
    * Store config values.
    *
    * @param boolean $result The result of saving values.
    */
    public function flush();

    /**
    * Returns all values.
    *
    * @return array All values
    */
    public function all();
}
