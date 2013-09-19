<?php

namespace Pum\Extension\EmFactory\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectLifecycleListener implements EventSubscriber
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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
            $this->eventDispatcher->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($insert));
        }

        foreach ($uow->getScheduledEntityUpdates() as $update) {
            $this->eventDispatcher->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($update));
        }

        foreach ($uow->getScheduledEntityDeletions() as $delete) {
            $this->eventDispatcher->dispatch(Events::OBJECT_DELETE, new ObjectEvent($delete));
        }

    }
}
