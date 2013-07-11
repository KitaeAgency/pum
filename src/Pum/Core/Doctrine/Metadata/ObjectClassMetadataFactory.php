<?php

namespace Pum\Core\Doctrine\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Pum\Core\Doctrine\Reflection\ObjectReflectionService;

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
