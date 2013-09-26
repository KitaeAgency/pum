<?php

namespace Pum\Extension\ProjectAdmin\Form\Listener;

use Pum\Core\Context\FieldContext;
use Pum\Core\ObjectFactory;
use Pum\Core\Object\Object;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PumObjectListener implements EventSubscriberInterface
{
    protected $factory;

    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

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

        if (!is_object($object)) {
            return;
        }

        list($project, $object) = $this->factory->getProjectAndObjectFromClass(get_class($object));

        $formView = $form->getConfig()->getOption('form_view');

        // map fields
        foreach ($object->getFields() as $field) {
            $typeHierarchy = $this->factory->getTypeHierarchy($field->getType(), 'Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface');
            $resolver = new OptionsResolver();
            foreach ($typeHierarchy as $type) {
                $type->setDefaultOptions($resolver);
            }
            $context = new FieldContext($project, $field, $resolver->resolve($field->getTypeOptions()));

            if (is_null($formView) || $formView->hasField($field)) {
                foreach ($typeHierarchy as $type) {
                    $type->buildForm($context, $form);
                }
            }
        }

        if ($form->getConfig()->getOption('with_submit')) {
            $form->add('submit', 'submit');
        }
    }
}
