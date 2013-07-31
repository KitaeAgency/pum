<?php

namespace Pum\Core\Extension;

use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\Events;
use Pum\Core\SchemaManager;

abstract class AbstractExtension implements ExtensionInterface
{
    protected $schemaManager;

    /**
     * {@inheritdoc}
     */
    public function setSchemaManager(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function getSchemaManager()
    {
        if (null === $this->schemaManager) {
            throw new \RuntimeException('Schema manager not injected in AbstractExtension');
        }

        return $this->schemaManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PROJECT_CHANGE => 'onProjectChange',
            Events::PROJECT_DELETE => 'onProjectDelete',
            Events::BEAM_CHANGE    => 'onBeamChange',
            Events::BEAM_DELETE    => 'onBeamDelete',
        );
    }

    public function onProjectChange(ProjectEvent $event)
    {
    }

    public function onProjectDelete(ProjectEvent $event)
    {
    }

    public function onBeamChange(BeamEvent $event)
    {
    }

    public function onBeamDelete(BeamEvent $event)
    {
    }
}
