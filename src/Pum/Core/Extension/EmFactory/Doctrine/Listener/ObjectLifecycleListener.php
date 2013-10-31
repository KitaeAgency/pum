<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Core\ObjectFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectLifecycleListener implements EventSubscriber
{
    protected $factory;
    protected $pendingInserts = array();

    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush', 'postFlush');
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->pendingInserts = $uow->getScheduledEntityInsertions();

        foreach ($uow->getScheduledEntityUpdates() as $update) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($update, $this->factory));
        }

        foreach ($uow->getScheduledEntityDeletions() as $delete) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_DELETE, new ObjectEvent($delete, $this->factory));
        }

    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($this->pendingInserts as $insert) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_CHANGE, new ObjectEvent($insert, $this->factory));
        }

        $this->pendingInserts = array();
    }
}
