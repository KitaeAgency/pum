<?php

namespace Pum\Core\BuilderRegistry;

use Pum\Core\Exception\CyclicTypeException;
use Pum\Core\Exception\TypeNotFoundException;
use Pum\Core\TypeExtensionInterface;
use Pum\Core\TypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerBuilderRegistry extends AbstractBuilderRegistry
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
    public function getTypeNames()
    {
        return array_keys($this->typeIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->typeIds[$name])) {
            throw new TypeNotFoundException($name);
        }

        $type = $this->container->get($this->typeIds[$name]);

        if (!$type instanceof TypeInterface) {
            throw new \RuntimeException(sprintf('The class "%s" does not implement TypeInterface.', get_class($type)));
        }
        if ($type->getName() !== $name) {
            throw new \RuntimeException(sprintf('Configured type "%s" to be named "%s", but actual name from getName() is "%s".', get_class($type), $name, $type->getName()));
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        if (!isset($this->typeExtensionIds[$name])) {
            return array();
        }

        $extensions = array_map(function ($id) {
            return $this->container->get($id);
        }, $this->typeExtensionIds[$name]);

        foreach ($extensions as $extension) {
            if (!$extension instanceof TypeExtensionInterface) {
                throw new \RuntimeException(sprintf('The class "%s" does not implement TypeExtensionInterface.', get_class($extension)));
            }
            if ($extension->getExtendedType() !== $name) {
                throw new \RuntimeException(sprintf('Configured type extension "%s" to extend "%s", but actual extension from getExtendedType() is "%s".', get_class($extension), $name, $extension->getExtendedType()));
            }
        }

        return $extensions;
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
