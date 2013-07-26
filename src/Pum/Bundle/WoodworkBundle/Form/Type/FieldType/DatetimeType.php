<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatetimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('restriction', 'choice', array(
                    'required' => false,
                    'choices'   => array('only_anterior' => 'Allow only anterior date', 'only_posterior' => 'Allow only posterior date'),
                    'empty_value' => 'No restriction',
            ))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_datetime';
    }
}
