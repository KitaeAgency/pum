<?php

namespace Pum\Core\Extension\Search\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\Extension\Search\SearchEngine;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IndexUpdateListener implements EventSubscriberInterface
{
    private $searchEngine;
    private $emFactory;
    private $objectFactory;

    public function __construct(SearchEngine $searchEngine, EmFactory $emFactory)
    {
        $this->searchEngine  = $searchEngine;
        $this->emFactory     = $emFactory;
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
            Events::OBJECT_CREATE  => 'onObjectChange',
            Events::OBJECT_CHANGE  => 'onObjectChange',
            Events::OBJECT_DELETE  => 'onObjectDelete',
        );
    }

    public function onObjectChange(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof SearchableInterface) {
            return;
        }

        $this->searchEngine->put($obj);
    }

    public function onObjectDelete(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof SearchableInterface) {
            return;
        }

        $this->searchEngine->delete($obj);
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
        // by now, ignore :)
    }

    public function onBeamChange(BeamEvent $event)
    {
        $factory = $event->getObjectFactory();
        $beam    = $event->getBeam();

        foreach ($beam->getProjects() as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
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
        foreach ($project->getObjects() as $object) {
            if (!$object->isSearchEnabled()) {
                continue;
            }

            $indexName = SearchEngine::getIndexName($project->getName());
            $typeName = SearchEngine::getTypeName($object->getName());

            $this->searchEngine->updateIndex($indexName, $typeName, $object);

            $em = $this->emFactory->getManager($objectFactory, $project->getName());

            $all = $em->getRepository($object->getName())->findAll();
            foreach ($all as $obj) {
                $this->searchEngine->put($obj);
            }
        }
    }
}
