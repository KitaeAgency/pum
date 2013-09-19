<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\SchemaManager;

class PumDefinitionDriver implements MappingDriver
{
    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $metadata->loadPum();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        throw new \RuntimeException('This operation is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return true;
    }
}
