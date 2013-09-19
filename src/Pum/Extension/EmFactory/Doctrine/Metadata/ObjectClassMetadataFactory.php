<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Pum\Core\Extension\EmFactory\Doctrine\Reflection\ObjectReflectionService;
use Pum\Core\SchemaManager;

/**
 * Overrides class metadata factory to use ObjectClassMetadata
 * for class's metadatas, used by EntityManager.
 */
class ObjectClassMetadataFactory extends ClassMetadataFactory
{
    public function __construct()
    {
        $this->setReflectionService(new ObjectReflectionService());
    }

    protected function newClassMetadataInstance($className)
    {
        return new ObjectClassMetadata($className);
    }
}
