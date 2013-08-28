<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Bundle\WoodworkBundle\Form\Listener\TypeOptionsListener;
use Pum\Core\SchemaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('type', 'ww_field_type', array('required' => false))
            ->addEventSubscriber(new TypeOptionsListener())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\FieldDefinition'
        ));
    }

    public function getName()
    {
        return 'ww_field_definition';
    }
}
