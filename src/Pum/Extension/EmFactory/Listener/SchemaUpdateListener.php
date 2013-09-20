<?php

namespace Pum\Extension\EmFactory\Listener;

use Pum\Core\Events;
use Pum\Extension\EmFactory\EmFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SchemaUpdateListener implements EventSubscriberInterface
{
    /**
     * @var EmFactory
     */
    protected $factory;

    public function __construct(EmFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
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
        $project = $event->getProject();

        $this->factory->getManager($project)->updateSchema();
    }

    public function onProjectDelete(ProjectEvent $event)
    {
        $factory = $event->getObjectFactory();
        $project = $event->getProject();

        $this->factory->getManager($project)->updateSchema();
    }

    public function onBeamChange(BeamEvent $event)
    {
        $factory = $event->getObjectFactory();
        $beam    = $event->getBeam();

        foreach ($beam->getProjects() as $project) {
            $this->factory->getManager($project)->updateSchema();
        }
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $manager = $event->getSchemaManager();
        $beam = $event->getBeam();

        foreach ($manager->getProjectsUsingBeam($beam) as $project) {
            $this->factory->getManager($project)->updateSchema();
        }
    }
}
