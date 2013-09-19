<?php

namespace Pum\Core\BuilderRegistry;

use Pum\Core\BuilderRegistryInterface;
use Pum\Core\Exception\TypeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerBuilderRegistry implements BuilderRegistryInterface
{
    protected $container;
    protected $typeIds;
    protected $typeExtensionIds;
    protected $behaviorIds;

    public function __construct(ContainerInterface $container, array $typeIds, array $typeExtensionIds, array $behaviorIds)
    {
        $this->container        = $container;
        $this->typeIds          = $typeIds;
        $this->typeExtensionIds = $typeExtensionIds;
        $this->behaviorIds      = $behaviorIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->typeIds[$name])) {
            throw new TypeNotFoundException($name);
        }

        return $this->container->get($this->typeIds[$name]);
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
    }

    /**
     * {@inheritdoc}
     */
    public function getBehavior($name)
    {
        if (!isset($this->behaviorIds[$name])) {
            throw new BehaviorNotFoundException($name);
        }

        return $this->container->get($this->behaviorIds[$name]);
    }
}
