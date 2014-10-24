<?php

namespace Pum\Core\BuilderRegistry;

/**
 * A build registry contains all data types and behaviors.
 *
 * It is also responsible of resolving data types.
 */
interface BuilderRegistryInterface
{
    /**
     * Returns instance of data type
     *
     * @return object
     */
    public function getType($name);

    /**
     * Returns names of all registered data types
     *
     * @return string[]
     */
    public function getTypeNames();

    /**
     * Returns instance of all registered data types extension of a type
     *
     * @return string[]
     */
    public function getTypeExtensions($name);

    /**
     * @return BehaviorInterface
     *
     * @throws DefinitionNotFoundException
     */
    public function getBehavior($name);

    /**
     * Returns names of all registered behaviors
     *
     * @return string[]
     */
    public function getBehaviorNames();

    /**
     * Returns all types and type extensions associated to a given type name.
     *
     * @param string $name       a type name
     * @param string $instanceOf filter types per class or interface name
     *
     * @return array
     *
     * @throws DefinitionNotFoundException
     */
    public function getHierarchy($name, $instanceOf = null);
}
