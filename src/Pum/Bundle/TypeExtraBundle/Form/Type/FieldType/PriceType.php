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
                        'EUR' => 'Euro',
                        'USD' => 'US Dollar'
                    ),
                    'empty_value' => 'Choose your currency',
            ))
            ->add('negatif', 'checkbox', array('label' => 'Allow negative price'))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_price';
    }
}
