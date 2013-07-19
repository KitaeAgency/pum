<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Pum\Core\Extension\EmFactory\Generator\ClassGenerator;
use Pum\Core\SchemaManager;

class PumDefinitionDriver implements MappingDriver
{
    protected $schemaManager;
    protected $classGenerator;
    protected $projectName;

    /**
     * Constructor.
     *
     * @param SchemaManager $schemaManager PUM schemaManager to use to get metadatas.
     */
    public function __construct(SchemaManager $schemaManager, ClassGenerator $classGenerator, $projectName)
    {
        $this->schemaManager  = $schemaManager;
        $this->classGenerator = $classGenerator;
        $this->projectName    = $projectName;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $name = $this->classGenerator->getNameFromClass($className);
        $def = $this->schemaManager->getDefinition($this->projectName, $name);

        $metadata->loadFromObjectDefinition($this->projectName, $def);
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
