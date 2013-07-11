<?php

namespace Pum\Core\EventListener;

use Doctrine\ORM\Tools\SchemaTool;
use Pum\Core\EventListener\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Updates database schema when a defintion is modified.
 */
class SchemaListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_DEFINITION_SAVE   => 'onObjectDefinitionChange',
            Events::OBJECT_DEFINITION_DELETE => 'onObjectDefinitionChange',
        );
    }

    public function onObjectDefinitionChange(ObjectDefinitionEvent $event)
    {
        $schemaTool = new SchemaTool($event->getEntityManager());
        $schemaTool->updateSchema(array($event->getClassMetadata()), true);
    }
}
