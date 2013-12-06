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
    static public function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_PRE_CREATE => 'onObjectChange',
            Events::OBJECT_CHANGE     => 'onObjectChange',
            Events::OBJECT_DELETE     => 'onObjectDelete',
        );
    }

    public function onObjectChange(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof FlushStorageInterface) {
            return;
        }
var_dump('here');
        $obj->flushToStorage($this->storage);
    }

    public function onObjectDelete(ObjectEvent $event)
    {
        $obj = $event->getObject();
        if (!$obj instanceof FlushStorageInterface) {
            return;
        }

        $obj->removeFromStorage($this->storage);
    }
}
