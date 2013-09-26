<?php

namespace Pum\Core\BuilderRegistry;

use Pum\Core\BuilderRegistryInterface;
use Pum\Core\Exception\TypeNotFoundException;

class StaticBuilderRegistry extends AbstractBuilderRegistry
{
    protected $types = array();
    protected $typeExtensions = array();
    protected $behaviors = array();

    public function __construct(array $types = array(), array $typeExtensions = array(), array $behaviors = array())
    {
        $this->types          = $types;
        $this->typeExtensions = $typeExtensions;
        $this->behaviors      = $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeNames()
    {
        return array_map(function ($type) {
            return $type->getName();
        }, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        foreach ($this->types as $type) {
            if ($type->getName() == $name) {
                return $type;
            }
        }
        throw new TypeNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        $result = array();

        foreach ($this->typeExtensions as $typeExtension) {
            if ($typeExtension->getExtendedType() == $name) {
                $result[] = $typeExtension;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getBehavior($name)
    {
        if (!isset($this->behaviors[$name])) {
            throw new BehaviorNotFoundException($name);
        }

        return $this->behaviors[$name];
    }
}
