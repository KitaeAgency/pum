<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('color', 'pum_color')
            ->add('icon', 'pum_icon')
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Beam',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_beam';
    }
}
