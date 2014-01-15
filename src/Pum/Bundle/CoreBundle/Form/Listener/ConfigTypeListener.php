<?php

namespace Pum\Bundle\CoreBundle\Form\Listener;

use Pum\Core\Config\ConfigInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConfigTypeListener implements EventSubscriberInterface
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onSetData',
            FormEvents::SUBMIT       => 'onSubmitData',
        );
    }

    public function onSetData(FormEvent $event)
    {
        $event->setData($this->config->all());

        $event->getForm()->add('save', 'submit');
    }

    public function onSubmitData(FormEvent $event)
    {
        $data = $event->getData();

        unset($data['save']);
        foreach($data as $key => $value) {
            $value = (is_array($value)) ? array_values($value) : $value;
            $this->config->set($key, $value);
        }

        $this->config->flush();
    }
}
