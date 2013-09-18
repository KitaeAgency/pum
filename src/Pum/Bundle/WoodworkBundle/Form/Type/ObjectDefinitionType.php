<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Pum\Core\Definition\TableView;

class ObjectDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('classname', 'text')
            ->add('fields', 'ww_field_definition_collection')
            ->add('save', 'submit')
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $object = $event->getData();

            $defaultName = TableView::DEFAULT_NAME;
            if ($object->hasTableView($defaultName)) {
                $object->removeTableView($object->getTableView($defaultName));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\ObjectDefinition'
        ));
    }

    public function getName()
    {
        return 'ww_object_definition';
    }
}
