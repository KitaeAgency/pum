<?php

namespace Pum\Core\Extension\Tree\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Extension\Tree\TreeableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TreeUpdateListener implements EventSubscriberInterface
{
    const MAX_ITEMS = 250;

    /**
     * @var EmFactory
     */
    protected $emFactory;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    public function __construct(EmFactory $emFactory)
    {
        $this->emFactory     = $emFactory;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_DEFINITION_TREE_UPDATE => 'onTreeUpdate',
            Events::OBJECT_PRE_CREATE             => 'onObjectCreate'
        );
    }

    public function onObjectCreate(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof TreeableInterface) {
            return;
        }

        $object = $event->getObjectFactory()->getDefinition($obj::PUM_PROJECT, $obj::PUM_OBJECT);

        if (!$object->isTreeEnabled()) {
            return;
        }

        if (null === $tree = $object->getTree()) {
            return;
        }

        if (null === $treeField = $tree->getTreeField()) {
            return;
        }

        $em            = $this->emFactory->getManager($event->getObjectFactory(), $obj::PUM_PROJECT);
        $repo          = $em->getRepository($obj::PUM_OBJECT);
        $parent_field  = $treeField->getTypeOption('inversed_by');
        $parent_getter = 'get'.ucfirst(Namer::toCamelCase($parent_field));
        $sequence      = 0;

        if (null != $parent_id = $obj->$parent_getter()) {
            $parent_id = $parent_id->getId();
        }

        $result = $repo->getObjectsBy(array($parent_field => $parent_id), array('treeSequence' => 'desc'), $limit = 1);
        if (count($result) === 1) {
            $result   = $result[0];
            $sequence = $result->getTreeSequence()+1;
        }

        $obj->setTreeSequence($sequence);
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

        // Tree sequence initialize
        if (null === $tree = $object->getTree()) {
            return;
        }

        if (null === $treeField = $tree->getTreeField()) {
            return;
        }

        $options = array(
            'parent_field' => $treeField->getTypeOption('inversed_by'),
        );

        $this->putSequenceForNode($em, $object, null, $options);
    }

    private function putSequenceForNode($em, $object, $node_id, $options)
    {
        $repo      = $em->getRepository($object->getName());
        $sequence  = 0;
        $count     = $repo->countBy(array($options['parent_field'] => $node_id));
        $iteration = ceil($count/self::MAX_ITEMS);

        for ($i = 0; $i < $iteration; $i++) {
            $objs = $repo->getObjectsBy(array($options['parent_field'] => $node_id), array('id' => 'asc'), $limit=self::MAX_ITEMS, $offset=$i*self::MAX_ITEMS);

            foreach ($objs as $obj) {
                $obj->setTreeSequence($sequence++);
            }

            $em->flush();
            $em->clear();
            gc_collect_cycles();

            foreach ($objs as $obj) {
                $this->putSequenceForNode($em, $object, $obj->getId(), $options);
            }
        }
    }
}
