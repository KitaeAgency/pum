<?php

namespace Pum\Core\Extension\Form\Form\Listener;

use Pum\Core\Object\Object;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PumObjectListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onSetData'
        );
    }

    public function onSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $object = $event->getData();

        if (!$object instanceof Object) {
            throw new \InvalidArgumentException(sprintf('Expected an object, got a "%s".', is_object($object) ? get_class($object) : gettype($object)));
        }

        $metadata = $object->__pum_getMetadata();
        foreach ($metadata->types as $name => $type) {
            $metadata->getType($name)->buildForm($form, $name, $metadata->typeOptions[$name]);
        }
    }
}
