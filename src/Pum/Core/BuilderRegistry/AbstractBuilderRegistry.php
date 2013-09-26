<?php

namespace Pum\Core\BuilderRegistry;

use Pum\Core\BuilderRegistryInterface;
use Pum\Core\Exception\CyclicTypeException;
use Pum\Core\Exception\TypeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBuilderRegistry implements BuilderRegistryInterface
{
    abstract public function getType($name);
    abstract public function getTypeExtensions($name);

    /**
     * {@inheritdoc}
     */
    public function getHierarchy($name, $instanceOf = null)
    {
        $resolving = array(); // debug array to avoid recursion
        $types = array();
        $typeExtensions = array();

        $tmpName = $name;
        while ($tmpName !== null) {
            if (in_array($tmpName, $resolving)) {
                throw new CyclicTypeException($tmpName, $resolving);
            }
            $resolving[] = $tmpName;

            $type = $this->getType($tmpName);
            $typeExtensions = $this->getTypeExtensions($tmpName);

            $types = array_merge($types, array_merge($typeExtensions, array($type)));

            $tmpName    = $type->getParent();
        }

        $types = array_reverse($types);

        if (null !== $instanceOf) {
            return array_filter($types, function ($type) use ($instanceOf) {
                return $type instanceof $instanceOf;
            });
        }

        return $types;
    }
}
