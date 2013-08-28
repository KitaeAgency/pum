<?php

namespace Pum\Bundle\WoodworkBundle\Form\Listener;

use Pum\Core\Definition\FieldDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TypeOptionsListener implements EventSubscriberInterface
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
        if (null === $data) {
            return;
        }

        if ($data instanceof FieldDefinition) {
            $type = $data->getType();
        } elseif (is_array($data) && isset($data['type'])) {
            $type = $data['type'];
        } else {
            throw new \RuntimeException('Invalid data type. Expected FieldDefinition or array with key type, got '.(is_object($data) ? get_class($data) : gettype($data)));
        }

        if (!$type) {
            throw new \RuntimeException('You need to specify a type');
        }

        $event->getForm()->add('type_options', 'pum_type_options', array('pum_type' => $type));
    }
}
