<?php

namespace Pum\Bundle\WoodworkBundle\Form\Listener;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\SchemaManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TypeOptionsListener implements EventSubscriberInterface
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onData',
            FormEvents::PRE_SUBMIT   => 'onSubmit',
        );
    }

    public function onData(FormEvent $event)
    {
        $data = $event->getData();
        if (null === $data || ! $data instanceof FieldDefinition) {
            return;
        }

        $type = $data->getType();
        if (!$type) {
            return;
        }
        $type = $this->schemaManager->getType($type)->getFormOptionsType($data);
        $event->getForm()->add('type_options', $type);
    }

    public function onSubmit(FormEvent $event)
    {
    }
}
