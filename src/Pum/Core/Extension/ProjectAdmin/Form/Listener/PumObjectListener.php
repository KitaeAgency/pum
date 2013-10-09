<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Listener;

use Pum\Core\Context\FieldContext;
use Pum\Core\ObjectFactory;
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
        $form   = $event->getForm();
        $object = $event->getData();

        if (!is_object($object)) {
            return;
        }

        list($project, $object) = $this->factory->getProjectAndObjectFromClass(get_class($object));

        $formView = $form->getConfig()->getOption('form_view');

        // map fields
        $fieldDisplayingSequence = 0;
        $displaySequence = array();
        foreach ($object->getFields() as $field) {
            $typeHierarchy = $this->factory->getTypeHierarchy($field->getType(), 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');
            $resolver = new OptionsResolver();
            foreach ($typeHierarchy as $type) {
                $type->setDefaultOptions($resolver);
            }

            $viewOptions = array();
            if (!is_null($formView) && $formView->hasField($field)) {
                $viewOptions = array(
                    'label'       => $formView->getField($field)->getLabel(),
                    'placeholder' => $formView->getField($field)->getPlaceholder()
                );
            }
            $context = new FieldContext($project, $field, array_merge($resolver->resolve($field->getTypeOptions()), $viewOptions));
            $context->setObjectFactory($this->factory);

            if (is_null($formView) || $formView->hasField($field)) {
                $fieldDisplayingSequence = is_null($formView) ? ++$fieldDisplayingSequence : $formView->getField($field)->getSequence();
                foreach ($typeHierarchy as $type) {
                    $displaySequence[$fieldDisplayingSequence.'_'.$field->getName()] = array('type' => $type, 'context' => $context);
                }
            }
        }
        ksort($displaySequence);
        foreach ($displaySequence as $sequence) {
            $sequence['type']->buildForm($sequence['context'], $form);
        }

        if ($form->getConfig()->getOption('with_submit')) {
            $form->add('submit', 'submit');
        }
    }
}
