<?php

namespace Pum\Bundle\TypeExtraBundle\Listener;

use Pum\Core\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MediaLifecycleListener implements EventSubscriberInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_PRE_CREATE => 'onObjectPrecreate',
            //Events::OBJECT_CREATE     => 'onObjectChange',
            Events::OBJECT_UPDATE     => 'onObjectChange',
            Events::OBJECT_DELETE     => 'onObjectDelete',
            Events::OBJECT_POST_LOAD  => 'onObjectLoad'
        );
    }

    public function onObjectPrecreate(ObjectEvent $event)
    {
        $this->onObjectLoad($event);
        $this->onObjectChange($event);
    }

    public function onObjectChange(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof FlushStorageInterface) {
            return;
        }

        if (null !== $obj->getStorageToRemove()) {
            $obj->removeFromStorage($this->storage);
        }

        $obj->flushToStorage($this->storage);
    }

    public function onObjectDelete(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof FlushStorageInterface) {
            return;
        }

        $obj->removeFromStorage($this->storage, $deleteAll = true);
    }

    public function onObjectLoad(ObjectEvent $event)
    {
        $obj = $event->getObject();

        if (!$obj instanceof FlushStorageInterface) {
            return;
        }

        $obj->setMediaMetadataStorage($this->storage->getMediaMetadataStorage());
    }
}
