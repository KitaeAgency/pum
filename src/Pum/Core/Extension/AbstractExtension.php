<?php

namespace Pum\Core\Extension;

use Pum\Core\SchemaManager;

class AbstractExtension implements ExtensionInterface
{
    protected $schemaManager;

    /**
     * {@inheritdoc}
     */
    public function setSchemaManager(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
        );
    }
}
