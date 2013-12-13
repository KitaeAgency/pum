<?php

namespace Pum\Core\Extension\EmFactory\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
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
            Events::PROJECT_CHANGE => 'onProjectChange',
            Events::PROJECT_DELETE => 'onProjectDelete',

            Events::BEAM_DELETE    => 'onBeamDelete',
        );
    }

    public function onProjectChange(ProjectEvent $event)
    {
        $project = $event->getProject();

        $this->updateProject($project, $event->getObjectFactory());
    }

    public function onProjectDelete(ProjectEvent $event)
    {
        $factory = $event->getObjectFactory();
        $project = $event->getProject();

        $this->updateProject($project, $event->getObjectFactory());
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $objectFactory = $event->getObjectFactory();
        $beam = $event->getBeam();

        foreach ($beam->getProjects() as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory)
    {
        $this->emFactory->getManager($objectFactory, $project)->updateSchema();
        $this->emFactory->getManager($objectFactory, $project)->clearCache();
    }
}
