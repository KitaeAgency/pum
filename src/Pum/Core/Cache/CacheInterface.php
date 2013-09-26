<?php

namespace Pum\Core\Cache;

/**
 * Class responsible of storing class definitions and loading them.
 */
interface CacheInterface
{
    /**
     * Returns a random string, being the same until the group is cleared.
     *
     * @param string $group
     *
     * @return string
     */ 
    public function getSalt($group = 'default');

    /**
     * Tests if cache contains a class definition.
     *
     * @param string $class the classname to look for
     * @param string $group
     *
     * @return boolean
     */
    public function hasClass($class, $group = 'default');

    /**
     * Searches and load class definition from cache.
     *
     * @throws ClassNotFoundException class was not found in cache
     */
    public function loadClass($class, $group = 'default');

    /**
     * Saves and load class definition to the cache.
     *
     * @param string $class the class name
     * @param string $content the class code
     * @param string $group
     */
    public function saveClass($class, $content, $group = 'default');

    /**
     * Clears all cache for a given group (should refresh salts).
     *
     * @param string $group
     */
    public function clear($group = 'default');

    /**
     * Clears EVERYTHING.
     */
    public function clearAllGroups();
}
