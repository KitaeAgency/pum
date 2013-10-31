<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Listener;

use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FormViewFieldOptionsListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'resizeForm',
            FormEvents::SUBMIT   => 'resizeForm',
        );
    }

    public function resizeForm(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data) {
            return;
        }

        if (!$data instanceof FormViewField) {
            var_dump($data);exit;
            throw new \RuntimeException('Invalid data type. Expected FieldDefinition or array with key type, got '.(is_object($data) ? get_class($data) : gettype($data)));
        }

        $field = $data->getField();
        if (!$field || !$field->getType()) {
            return; // nothing to do here, no type, no options
        }

        $event->getForm()->add('options', 'pa_formview_field_options', array('pum_type' => $field->getType(), 'form_view_field' => $data));
    }
}
