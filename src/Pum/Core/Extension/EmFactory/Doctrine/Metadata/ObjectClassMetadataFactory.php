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
    protected $schemaManager;

    public function __construct()
    {
        $this->setReflectionService(new ObjectReflectionService());
    }

    public function setSchemaManager(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    protected function newClassMetadataInstance($className)
    {
        return new ObjectClassMetadata($this->schemaManager, $className);
    }
}
