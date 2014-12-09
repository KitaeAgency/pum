<?php

namespace Pum\Core\Extension\Tree\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Pum\Core\Extension\Tree\TreeApi;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TreeUpdateListener implements EventSubscriberInterface
{
    const MAX_ITEMS = 10;

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
        $em   = $this->emFactory->getManager($objectFactory, $project->getName());
        $repo = $em->getRepository($object->getName());

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Tree sequence initialize
        if (null === $tree = $object->getTree()) {
            return;
        }

        if (null === $treeField = $tree->getTreeField()) {
            return;
        }

        if (null === $labelField = $tree->getLabelField()) {
            $labelField = 'id';
        } else {
            $labelField = $labelField->getName();
        }

        $options = array(
            'label_field'    => $labelField,
            'parent_field'   => $treeField->getTypeOption('inversed_by'),
            'children_field' => $treeField->getName(),
        );

        $this->putSequenceForNode($repo, null, $options);
    }

    private function putSequenceForNode($repo, $node_id, $options)
    {
        /*$sequence = 0;

        $count     = $repo->countBy(array($options['parent_field'] => $node_id));
        $iteration = ceil($count/self::MAX_ITEMS);

        for ($i = 0; $i < $iteration; $i++) {
            $objs = $repo->findBy(array($options['parent_field'] => $node_id), null, $limit=self::MAX_ITEMS, $offset=$i*self::MAX_ITEMS);
        }

        var_dump($count, $iteration, array($options['parent_field'] => $node_id));

        die('end');*/
    }

    private function getFieldGetter($fieldName)
    {
        return 'get'.ucfirst(Namer::toCamelCase($fieldName));
    }

    private function getFieldSetter($fieldName)
    {
        return 'set'.ucfirst(Namer::toCamelCase($fieldName));
    }
}
