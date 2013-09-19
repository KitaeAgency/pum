<?php

namespace Pum\Core\BuilderRegistry;

use Pum\Core\BuilderRegistryInterface;
use Pum\Core\Exception\TypeNotFoundException;

class StaticBuilderRegistry implements BuilderRegistryInterface
{
    protected $types = array();
    protected $typeExtensions = array();
    protected $behaviors = array();

    /**
     * {@inheritdoc}
     */
    public function getTypeNames()
    {
        return array_keys($this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            throw new TypeNotFoundException($name);
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeHierarchy($name)
    {
        $types = array();
        while ($name !== null) {
            $types[] = $this->getType($name);
            $name = $type->getParent();
        }

        return array_reverse($types);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        die('@todo');
    }

    /**
     * {@inheritdoc}
     */
    public function getBehavior($name)
    {
        if (!isset($this->behaviors[$name])) {
            throw new BehaviorNotFoundException($name);
        }
    }
}
