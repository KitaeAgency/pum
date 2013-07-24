<?php

namespace Pum\Core\Extension;

use Pum\Core\SchemaManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface ExtensionInterface extends EventSubscriberInterface
{
    public function setSchemaManager(SchemaManager $schemaManager);

    public function getName();
}
