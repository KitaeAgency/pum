<?php

namespace Pum\Bundle\TypeExtraBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pum\Bundle\TypeExtraBundle\Model\Media;

class MediaDeleteListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData',
        );
    }

    public function postSetData(FormEvent $event)
    {
        foreach ($event->getForm()->all() as $child) {
            if ($child->getData() instanceof Media) {
                $child->add('delete', 'checkbox', array(
                    'label'  => 'pum.form.pum_object.pum_media.delete.label',
                    'mapped' => false,
                ));
            }
        }
    }

}
