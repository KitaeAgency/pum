<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Listener;

use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormView;
use Pum\Core\ObjectFactory;
use Pum\Core\Events;
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
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
            FormEvents::SUBMIT        => 'submit',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    public function preSetData(FormEvent $event)
    {
        $this->dispatchEvent(FormEvents::PRE_SET_DATA, $event);

        $object  = $event->getData();
        $form    = $event->getForm();

        if (!$object) {
            return;
        }

        list($project, $object) = $this->factory->getProjectAndObjectFromClass(get_class($object));

        $formView = $form->getConfig()->getOption('form_view');

        if (is_string($formView)) {
            $formView = $object->getFormView($formView);
        }

        // map fields
        if (null !== $formView) {
            if ($formView instanceof FormView) {
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
            } else {
                throw new \RuntimeException('Invalid option. Expected FormView or string, got '.(is_object($formView) ? get_class($formView) : gettype($formView)));
            }
        }

        if ($form->getConfig()->getOption('with_submit')) {
            $form->add('submit', 'submit', array(
                'translation_domain' => 'pum_form'
            ));
        }
    }

    public function postSetData(FormEvent $event)
    {
        $this->dispatchEvent(FormEvents::POST_SET_DATA, $event);
    }

    public function preSubmit(FormEvent $event)
    {
        $this->dispatchEvent(FormEvents::PRE_SUBMIT, $event);
    }

    public function submit(FormEvent $event)
    {
        $this->dispatchEvent(FormEvents::SUBMIT, $event);
    }

    public function postSubmit(FormEvent $event)
    {
        $this->dispatchEvent(FormEvents::POST_SUBMIT, $event);
    }

    protected function dispatchEvent($eventName, FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();

        if ($options['dispatch_events'] && $eventName = $this->getPumEvent($eventName)) {
            $this->factory->getEventDispatcher()->dispatch($eventName, $event);
        }
    }

    protected function getPumEvent($eventName)
    {
        $events = array(
            FormEvents::PRE_SET_DATA  => Events::OBJECT_FORM_PRE_SET_DATA,
            FormEvents::POST_SET_DATA => Events::OBJECT_FORM_POST_SET_DATA,
            FormEvents::PRE_SUBMIT    => Events::OBJECT_FORM_PRE_SUBMIT,
            FormEvents::SUBMIT        => Events::OBJECT_FORM_SUBMIT,
            FormEvents::POST_SUBMIT   => Events::OBJECT_FORM_POST_SUBMIT
        );

        if (isset($events[$eventName])) {
            return $events[$eventName];
        }

        return null;
    }
}
