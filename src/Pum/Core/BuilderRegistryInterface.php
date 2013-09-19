<?php

namespace Pum\Core;

interface BuilderRegistryInterface
{
    /**
     * @return array
     */
    public function getTypeNames();

    /**
     * Returns all types and type extensions associated to a given name.
     *
     * @param string $name       a type name
     * @param string $instanceOf a class or interface name
     *
     * @return array
     *
     * @throws DefinitionNotFoundException
     */
    public function getHierarchy($name, $instanceOf = null);

    /**
     * @return BehaviorInterface
     *
     * @throws DefinitionNotFoundException
     */
    public function getBehavior($name);
}
