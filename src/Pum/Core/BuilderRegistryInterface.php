<?php

namespace Pum\Core;

interface BuilderRegistryInterface
{
    /**
     * @return TypeInterface
     *
     * @throws DefinitionNotFoundException
     */
    public function getType($name);

    /**
     * @return array
     *
     * @throws DefinitionNotFoundException
     */
    public function getTypeHierarchy($name);

    /**
     * @return array an array of TypeExtensionInterface
     */
    public function getTypeExtensions($name);

    /**
     * @return BehaviorInterface
     *
     * @throws DefinitionNotFoundException
     */
    public function getBehavior($name);
}
