<?php

namespace Pum\Core\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Pum\Core\Manager;

class PumDefinitionDriver implements MappingDriver
{
    protected $manager;

    /**
     * Constructor.
     *
     * @param Manager $manager PUM manager to use to get metadatas.
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $def = $this->manager->getDefinitionFromClassName($className);

        $metadata->loadFromObjectDefinition($def);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        return $this->manager->getAllDefinitionNames();
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return true;
    }
}
