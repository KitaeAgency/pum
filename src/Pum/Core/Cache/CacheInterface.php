<?php

namespace Pum\Core\Cache;

/**
 * Class responsible of storing class definitions and loading them.
 */
interface CacheInterface
{
    /**
     * Tests if cache contains a class definition.
     *
     * @param string $class the classname to look for
     * @param string $group
     *
     * @return boolean
     */
    public function hasClass($class);

    /**
     * Searches and load class definition from cache.
     *
     * @throws ClassNotFoundException class was not found in cache
     */
    public function loadClass($class);

    /**
     * Saves and load class definition to the cache.
     *
     * @param string $class the class name
     * @param string $content the class code
     * @param string $group
     */
    public function saveClass($class, $content);

    /**
     * Clears all cache for a given directory.
     *
     * @param string $directory
     */
    public function clear($directory = null);
}
