<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateType extends AbstractType
{
    const ANTERIOR_DATE  = 'only_anterior';
    const POSTERIOR_DATE = 'only_posterior';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('restriction', 'choice', array(
                    'required' => false,
                    'choices'   => array(
                            self::ANTERIOR_DATE  => 'Allow only anterior date',
                            self::POSTERIOR_DATE => 'Allow only posterior date'
                    ),
                    'empty_value' => 'No restriction',
            ))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_date';
    }
}
