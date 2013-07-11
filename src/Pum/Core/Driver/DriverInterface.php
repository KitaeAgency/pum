<?php

namespace Pum\Core\Driver;
use Pum\Core\Definition\ObjectDefinition;

interface DriverInterface
{
    /**
     * Returns an array with available definitions.
     *
     * @return array
     */
    public function getAllDefinitionNames();

    /**
     * Returns a given definition.
     *
     * @return ObjectDefinition
     *
     * @throws Pum\Core\Exception\DefinitionNotFoundException
     */
    public function getDefinition($name);

    /**
     * Deletes an object definition.
     */
    public function delete(ObjectDefinition $definition);

    /**
     * Saves an object definition.
     */
    public function save(ObjectDefinition $definition);
}
