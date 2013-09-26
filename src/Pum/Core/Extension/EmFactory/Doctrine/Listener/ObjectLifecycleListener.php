<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectLifecycleListener implements EventSubscriber
{
    protected $factory;

    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $insert) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($insert, $this->factory));
        }

        foreach ($uow->getScheduledEntityUpdates() as $update) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($update, $this->factory));
        }

        foreach ($uow->getScheduledEntityDeletions() as $delete) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_DELETE, new ObjectEvent($delete, $this->factory));
        }

    }
}
