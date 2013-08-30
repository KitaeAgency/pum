<?php

namespace Pum\CoreBundle\Form\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConfigTypeListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onSetData',
            FormEvents::PRE_SUBMIT   => 'onSubmitData',
        );
    }

    public function onSetData(FormEvent $event)
    {
        
    }

    public function onSubmitData(FormEvent $event)
    {
        
    }
}
