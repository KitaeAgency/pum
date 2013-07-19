<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Reflection;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionClass;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionProperty;

class ObjectReflectionService implements ReflectionService
{
    /**
     * {@inheritDoc}
     */
    public function getParentClasses($class)
    {
        if ( ! class_exists($class)) {
            throw MappingException::nonExistingClass($class);
        }

        return class_parents($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName($class)
    {
        $reflectionClass = new ObjectReflectionClass($class);

        return $reflectionClass->getShortName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace($class)
    {
        $reflectionClass = new ObjectReflectionClass($class);

        return $reflectionClass->getNamespaceName();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass($class)
    {
        return new ObjectReflectionClass($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty($class, $property)
    {
        $reflectionProperty = new ObjectReflectionProperty($class, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod($class, $method)
    {
        return method_exists($class, $method) && is_callable(array($class, $method));
    }
}
