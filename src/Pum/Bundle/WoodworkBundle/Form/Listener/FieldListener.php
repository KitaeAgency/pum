<?php

namespace Pum\Bundle\WoodworkBundle\Form\Listener;

use Pum\Core\Definition\FieldDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FieldListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'resizeForm',
            FormEvents::PRE_SUBMIT   => 'resizeForm',
        );
    }

    public function resizeForm(FormEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof FieldDefinition) {
            $type = $data->getType();
        } elseif (is_array($data) && isset($data['type'])) {
            $type = $data['type'];
        } else {
            $type = null;
        }

        if ($type != FieldDefinition::RELATION_TYPE) {
            $event->getForm()
                ->add('name', 'text')
                ->add('type', 'ww_field_type', array('empty_value' => true))
            ;
        } else {
            $event->getForm()
                ->add('name', 'hidden')
                ->add('type', 'hidden')
            ;
        }
    }
}
