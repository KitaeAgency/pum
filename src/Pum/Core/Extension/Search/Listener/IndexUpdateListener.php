<?php

namespace Pum\Core\Extension\Search\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
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
            Events::OBJECT_UPDATE  => 'onObjectChange',
            Events::OBJECT_DELETE  => 'onObjectDelete',

            Events::OBJECT_DEFINITION_SEARCH_UPDATE => 'onSearchUpdate',
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

    public function onSearchUpdate(ObjectDefinitionEvent $event)
    {
        $projects = $event->getObjectDefinition()->getBeam()->getProjects();

        foreach ($projects as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory)
    {
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
