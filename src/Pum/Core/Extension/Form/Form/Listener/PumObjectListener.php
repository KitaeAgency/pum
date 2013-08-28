<?php

namespace Pum\Core\Extension\Form\Form\Listener;

use Pum\Core\Definition\Relation;
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

        $metadata = $object->_pumGetMetadata();

        // map relations
        foreach ($metadata->relations as $name => $relation) {
            $form->add($name, 'pum_object_entity', array(
                'class'    => $relation['toClass'],
                'multiple' => in_array($relation['type'] , array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY)),
                'project'  => $object::__PUM_PROJECT_NAME
            ));
        }

        // map fields
        foreach ($metadata->types as $name => $type) {
            $metadata->getType($name)->buildForm($form, $name, $metadata->typeOptions[$name]);
        }

        $options = $form->getConfig()->getOptions();
        if ($options['with_submit']) {
            $form->add('submit', 'submit');
        }
    }
}
