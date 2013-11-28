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
        if (!$obj instanceof RoutableInterface) {
            return;
        }

        $signature = $obj::PUM_OBJECT.':'.$obj->getId();
        $this->routingFactory->getRouting($obj::PUM_PROJECT)->deleteByValue($signature);
        $this->routingFactory->getRouting($obj::PUM_PROJECT)->add($obj->getSeoKey(), $signature);
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

        // Redondance with ObjectFactory:233
        /*foreach ($beam->getProjects() as $project) {
            $this->updateProject($project, $event->getObjectFactory());
        }*/
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
        $routing = $this->routingFactory->getRouting($project->getName());
        $em      = $this->emFactory->getManager($objectFactory, $project->getName());

        $routing->purge();
        foreach ($project->getObjects() as $object) {
            if (!$object->isSeoEnabled()) {
                continue;
            }

            $all = $em->getRepository($object->getName())->findAll();
            foreach ($all as $obj) {
                $signature = $obj::PUM_OBJECT.':'.$obj->getId();
                $routing->add($obj->getSeoKey(), $signature);
            }
        }
    }
}
