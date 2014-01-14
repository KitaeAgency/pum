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
            Events::OBJECT_CREATE  => 'onObjectChange',
            Events::OBJECT_CHANGE  => 'onObjectChange',
            Events::OBJECT_DELETE  => 'onObjectDelete',

            Events::BEAM_DELETE    => 'onBeamDelete',

            Events::PROJECT_CHANGE => 'onProjectChange',
            Events::PROJECT_DELETE => 'onProjectDelete',
        );
    }

    public function onObjectChange(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof SearchableInterface || !$obj->getId()) {
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

    public function onBeamDelete(BeamEvent $event)
    {
        $objectFactory = $event->getObjectFactory();
        $beam = $event->getBeam();

        foreach ($beam->getObjects() as $object) {
            if ($object->isSearchEnabled()) {
                $object->storeEvent(Events::INDEX_DELETE);
            }
        }

        foreach ($beam->getProjects() as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory)
    {
        foreach ($project->getEvents() as $event) {
            if ($event === Events::INDEX_CHANGE || $event === Events::INDEX_DELETE) {
                $indexName = SearchEngine::getIndexName($project->getName());
                if ($this->searchEngine->existsIndex($indexName)) {
                    $this->searchEngine->deleteIndex($indexName);
                }

                foreach ($project->getObjects() as $object) {
                    if (!$object->isSearchEnabled()) {
                        continue;
                    }

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
    }
}
