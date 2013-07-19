<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Pum\Core\SchemaManager;

class PumDefinitionDriver implements MappingDriver
{
    protected $schemaManager;
    protected $projectName;

    /**
     * Constructor.
     *
     * @param SchemaManager $schemaManager PUM schemaManager to use to get metadatas.
     */
    public function __construct(SchemaManager $schemaManager, $projectName)
    {
        $this->schemaManager = $schemaManager;
        $this->projectName   = $projectName;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $def = $this->schemaManager->getDefinitionFromClassName($className);

        $metadata->loadFromObjectDefinition($def);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        return $this->schemaManager->getProject($this->projectName)->getDefinitionNames();
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return true;
    }
}
