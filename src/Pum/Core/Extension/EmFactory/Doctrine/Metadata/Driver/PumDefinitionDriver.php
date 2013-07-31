<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\SchemaManager;

class PumDefinitionDriver implements MappingDriver
{
    protected $schemaManager;
    protected $objectFactory;
    protected $projectName;

    /**
     * Constructor.
     *
     * @param SchemaManager $schemaManager PUM schemaManager to use to get metadatas.
     */
    public function __construct(SchemaManager $schemaManager, ObjectFactory $objectFactory, $projectName)
    {
        $this->schemaManager = $schemaManager;
        $this->objectFactory = $objectFactory;
        $this->projectName   = $projectName;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $name    = $this->objectFactory->getNameFromClass($className);
        $project = $this->schemaManager->getProject($this->projectName);
        $def     = $project->getObject($name);

        $metadata->loadFromObjectDefinition($project, $def);
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
