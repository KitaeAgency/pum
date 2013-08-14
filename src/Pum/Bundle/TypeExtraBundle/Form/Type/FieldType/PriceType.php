<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', 'choice', array(
                    'choices'   => array(
                        'EUR' => 'EUR',
                        'USD' => 'USD'
                    ),
                    'empty_value' => 'Choose your currency',
            ))
            ->add('negative', 'checkbox', array('label' => 'Allow negative price'))
            ->add('precision', 'number', array('required' => false))
            ->add('scale', 'number', array('label' => 'Decimal', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_price';
    }
}
