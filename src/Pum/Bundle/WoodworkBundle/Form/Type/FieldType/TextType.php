<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('length', 'number', array('required' => false))
            ->add('min_length', 'number', array('required' => false))
            ->add('max_length', 'number', array('required' => false))
            ->add('multi_lines', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_text';
    }
}
