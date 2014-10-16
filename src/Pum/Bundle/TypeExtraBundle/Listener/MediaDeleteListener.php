<?php

namespace Pum\Bundle\TypeExtraBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface;

class MediaDeleteListener implements EventSubscriberInterface
{
    const DELETE_FIELD = 'delete';

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::SUBMIT        => 'submit',
        );
    }

    public function postSetData(FormEvent $event)
    {
        foreach ($event->getForm()->all() as $child) {
            if ($child->getData() instanceof Media) {
                $child->add(self::DELETE_FIELD, 'checkbox', array(
                    'label'  => 'pum.form.pum_object.pum_media.delete.label',
                    'mapped' => false,
                ));
            }
        }
    }

    public function submit(FormEvent $event)
    {
        $obj = $event->getData();

        if ($obj instanceof FlushStorageInterface) {
            foreach ($event->getForm()->all() as $child) {
                if ($child->getData() instanceof Media) {
                    if ($child->has(self::DELETE_FIELD) && $child->get(self::DELETE_FIELD)->getData() && $child->getData()->getId()) {
                        $remover = 'remove'.ucfirst($child->getName());
                        $obj->$remover();
                    }
                }
            }
        }
    }
}
