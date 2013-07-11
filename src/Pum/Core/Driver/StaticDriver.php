<?php

namespace Pum\Core\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;

/**
 * Implementation of driver using a PHP array (no persistence).
 */
class StaticDriver implements DriverInterface
{
    protected $definitions;

    /**
     * {@inheritdoc}
     */
    public function getAllDefinitionNames()
    {
        return array_keys($this->definitions);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (!isset($this->definitions[$name])) {
            throw new DefinitionNotFoundException($name, $this->getAllDefinitionNames());
        }

        return $this->definitions[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function save(ObjectDefinition $definition)
    {
        $this->definitions[$definition->getName()] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ObjectDefinition $definition)
    {
        unset($this->definitions[$definition->getName()]);
    }
}
