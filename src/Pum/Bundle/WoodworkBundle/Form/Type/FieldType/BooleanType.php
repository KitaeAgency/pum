<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_boolean';
    }
}