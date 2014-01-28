<?php

namespace Pum\Core\Extension\EmFactory\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Pum\Core\Definition\EventObject;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\ObjectFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DomainEventsListener implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            /*'postPersist',*/ 'onFlush'
        );
    }

    /*public function postPersist(LifecycleEventArgs $args)
    {
        $objectFactory = $this->container->get('pum');
        $dispatcher = $objectFactory->getEventDispatcher();

        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if (!$entity instanceof EventObject) {
            return;
        }

        foreach($entity->popEvents() as $row) {
            list($name, $event) = $row;
            $event->setObjectFactory($objectFactory);
            $dispatcher->dispatch($name, $event);
        }
    }*/

    public function onFlush(OnFlushEventArgs $args)
    {
        $objectFactory = $this->container->get('pum');
        $dispatcher    = $objectFactory->getEventDispatcher();
        $em            = $args->getEntityManager();
        $uow           = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof EventObject) {
                return;
            }

            foreach($entity->popEvents() as $row) {
                list($name, $event) = $row;
                $event->setObjectFactory($objectFactory);
                $dispatcher->dispatch($name, $event);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof EventObject) {
                return;
            }

            foreach($entity->popEvents() as $row) {
                list($name, $event) = $row;
                $event->setObjectFactory($objectFactory);
                $dispatcher->dispatch($name, $event);
            }
        }

    }
}
