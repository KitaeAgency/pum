<?php

namespace Pum\Core\Doctrine\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Pum\Core\Doctrine\Reflection\ObjectReflectionService;
use Pum\Core\Manager;

/**
 * Overrides class metadata factory to use ObjectClassMetadata
 * for class's metadatas, used by EntityManager.
 */
class ObjectClassMetadataFactory extends ClassMetadataFactory
{
    protected $manager;

    public function __construct()
    {
        $this->setReflectionService(new ObjectReflectionService());
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function newClassMetadataInstance($className)
    {
        return new ObjectClassMetadata($this->manager, $className);
    }
}
