<?php

namespace Pum\Core\Extension\Routing\Listener;

use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingFactory;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoutingUpdateListener implements EventSubscriberInterface
{
    const MAX_ITEMS = 250;

    /**
     * @var RoutingFactory
     */
    protected $routingFactory;

    /**
     * @var EmFactory
     */
    protected $emFactory;

    public function __construct(RoutingFactory $routingFactory, EmFactory $emFactory)
    {
        $this->routingFactory = $routingFactory;
        $this->emFactory      = $emFactory;
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_INSERT     => 'onObjectChange',
            Events::OBJECT_UPDATE     => 'onObjectChange',
            Events::OBJECT_DELETE     => 'onObjectDelete',
            Events::BEAM_DELETE       => 'onBeamDelete',
            Events::PROJECT_UPDATE    => 'onProjectChange',
            Events::PROJECT_DELETE    => 'onProjectDelete',
        );
    }

    public function onObjectChange(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof RoutableInterface || !$obj->getId()) {
            return;
        }

        $signature = $obj::PUM_OBJECT.':'.$obj->getId();
        $this->routingFactory->getRouting($obj::PUM_PROJECT)->deleteByValue($signature);
        if ($obj->getObjectSlug()) {
            $obj->setObjectSlug($this->routingFactory->getRouting($obj::PUM_PROJECT)->add($obj->getSeoKey(), $signature));
        }
    }

    public function onObjectDelete(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof RoutableInterface) {
            return;
        }

        $signature = $obj::PUM_OBJECT.':'.$obj->getId();
        $this->routingFactory->getRouting($obj::PUM_PROJECT)->deleteByValue($signature);
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $objectFactory = $event->getObjectFactory();
        $beam = $event->getBeam();

        foreach ($beam->getObjects() as $object) {
            if ($object->isSeoEnabled()) {
                $object->storeEvent(Events::ROUTING_DELETE);
            }
        }

        foreach ($beam->getProjects() as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }
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

        $routing = $this->routingFactory->getRouting($project->getName());
        $routing->purge();
    }

    private function updateProject(Project $project, ObjectFactory $objectFactory)
    {
        $em      = $this->emFactory->getManager($objectFactory, $project->getName());
        $routing = $this->routingFactory->getRouting($project->getName());

        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $routing->purge();

        foreach ($project->getObjects() as $object) {
            if (!$object->isSeoEnabled()) {
                continue;
            }

            $repo      = $em->getRepository($object->getName());
            $count     = $repo->countBy();
            $iteration = ceil($count/self::MAX_ITEMS);

            for ($i = 0; $i < $iteration; $i++) {
                foreach ($repo->getObjectsBy(array(), null, $limit=self::MAX_ITEMS, $offset=$i*self::MAX_ITEMS) as $obj) {
                    $signature = $obj::PUM_OBJECT.':'.$obj->getId();
                    if ($obj->getObjectSlug()) {
                        $obj->setObjectSlug($routing->add($obj->getSeoKey(), $signature));
                    }
                }

                $em->clear();
                gc_collect_cycles();
            }
        }
    }
}
