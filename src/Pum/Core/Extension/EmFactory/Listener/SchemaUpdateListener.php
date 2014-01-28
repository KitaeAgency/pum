<?php

namespace Pum\Core\Extension\EmFactory\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\FieldDefinitionEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Event\ProjectBeamEvent;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SchemaUpdateListener implements EventSubscriberInterface
{
    /**
     * @var EmFactory
     */
    protected $emFactory;

    protected $updated = array();

    public function __construct(EmFactory $emFactory)
    {
        $this->emFactory = $emFactory;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            Events::PROJECT_UPDATE       => 'onProjectChange',
            Events::PROJECT_BEAM_ADDED   => 'onProjectBeamChange',
            Events::PROJECT_BEAM_REMOVED => 'onProjectBeamChange',
            Events::PROJECT_DELETE       => 'onProjectChange',

            Events::BEAM_UPDATE         => 'onBeamChange',
            Events::BEAM_OBJECT_ADDED   => 'onObjectDefinitionChange',
            Events::BEAM_OBJECT_REMOVED => 'onObjectDefinitionChange',
            Events::BEAM_DELETE         => 'onBeamChange',

            Events::OBJECT_DEFINITION_CREATE        => 'onObjectDefinitionChange',
            Events::OBJECT_DEFINITION_UPDATE        => 'onObjectDefinitionChange',
            Events::OBJECT_DEFINITION_FIELD_ADDED   => 'onFieldDefinitionChange',
            Events::OBJECT_DEFINITION_FIELD_REMOVED => 'onFieldDefinitionChange',
            Events::OBJECT_DEFINITION_DELETE        => 'onObjectDefinitionChange',
        );
    }

    public function onProjectBeamChange(ProjectBeamEvent $event)
    {
        $this->updateProject($event->getProject(), $event->getObjectFactory());
    }

    public function onProjectChange(ProjectEvent $event)
    {
        $this->updateProject($event->getProject(), $event->getObjectFactory());
    }

    public function onBeamChange(BeamEvent $event)
    {
        $projects = $event->getBeam()->getProjects();

        foreach ($projects as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    public function onObjectDefinitionChange(ObjectDefinitionEvent $event)
    {
        $projects = $event->getObjectDefinition()->getBeam()->getProjects();

        foreach ($projects as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    public function onFieldDefinitionChange(FieldDefinitionEvent $event)
    {
        $projects = $event->getFieldDefinition()->getObject()->getBeam()->getProjects();

        foreach ($projects as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory)
    {
        /* Update only once by project/process */
        if (!isset($this->updated[$project->getName()])) {
            $this->updated[$project->getName()] = true;

            $this->emFactory->getManager($objectFactory, $project)->updateSchema();
            $this->emFactory->getManager($objectFactory, $project)->clearCache();
        }
    }
}
