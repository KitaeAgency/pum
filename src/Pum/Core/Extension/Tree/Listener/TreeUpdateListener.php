<?php

namespace Pum\Core\Extension\Tree\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TreeUpdateListener implements EventSubscriberInterface
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
            Events::OBJECT_DEFINITION_TREE_UPDATE => 'onTreeUpdate',
        );
    }

    public function onTreeUpdate(ObjectDefinitionEvent $event)
    {
        $projects      = $event->getObjectDefinition()->getBeam()->getProjects();
        $objectFactory = $event->getObjectFactory();
        $object        = $event->getObjectDefinition();

        if (!$object->isTreeEnabled()) {
            return;
        }

        foreach ($projects as $project) {
            $this->updateProject($project, $objectFactory, $object);
        }
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory, ObjectDefinition $object)
    {
        $em = $this->emFactory->getManager($objectFactory, $project->getName());
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // TODO sequence initialize
    }
}
