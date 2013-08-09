<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoordinateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_coordinate';
    }
}
