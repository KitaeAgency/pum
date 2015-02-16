<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
        return array('onFlush', 'postFlush', 'postLoad');
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // objects about to be inserted don't have IDs
        $this->pendingInserts = $uow->getScheduledEntityInsertions();

        foreach ($this->pendingInserts as $insert) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_PRE_CREATE, new ObjectEvent($insert, $this->factory));
            $metadata = $em->getClassMetadata(get_class($insert));
            $uow->recomputeSingleEntityChangeset($metadata, $insert);
        }

        // we manually compute changeset because we NEED to change object before
        // they're persisted.
        foreach ($uow->getScheduledEntityUpdates() as $update) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_UPDATE, new ObjectEvent($update, $this->factory));
            $metadata = $em->getClassMetadata(get_class($update));
            $uow->recomputeSingleEntityChangeset($metadata, $update);
        }

        foreach ($uow->getScheduledEntityDeletions() as $delete) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_DELETE, new ObjectEvent($delete, $this->factory));
        }

    }

    public function postFlush(PostFlushEventArgs $args)
    {
        // here, we have IDs
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($this->pendingInserts as $insert) {
            $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_INSERT, new ObjectEvent($insert, $this->factory));
        }

        $this->pendingInserts = array();
    }

    /**
     * Raise Pum "OBJECT_POST_LOAD" event on object load
     * @param   LivecycleEventArgs  $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $this->factory->getEventDispatcher()->dispatch(Events::OBJECT_POST_LOAD, new ObjectEvent($args->getObject(), $this->factory));
    }
}
