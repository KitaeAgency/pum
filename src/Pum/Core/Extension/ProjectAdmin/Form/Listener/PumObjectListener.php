<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Listener;

use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormView;
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
        if (! $formView instanceof FormView) {
            throw new \RuntimeException('Invalid option. Expected FormView, got '.(is_object($formView) ? get_class($formView) : gettype($formView)));
        }

        // map fields
        foreach ($formView->getFields() as $formViewField) {
            $field = $formViewField->getField();
            
            $typeHierarchy = $this->factory->getTypeHierarchy($field->getType(), 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');
            $resolver = new OptionsResolver();
            foreach ($typeHierarchy as $type) {
                $type->setDefaultOptions($resolver);
            }

            $context = new FieldContext($project, $field, $resolver->resolve($field->getTypeOptions()));
            $context->setObjectFactory($this->factory);

            foreach ($typeHierarchy as $type) {
                $type->buildForm($context, $form, $formViewField);
            }
        }

        if ($form->getConfig()->getOption('with_submit')) {
            $form->add('submit', 'submit');
        }
    }
}
