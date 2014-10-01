<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('disabled' => true))
            ->add('beams', 'entity', array(
                'class'    => 'Pum\Core\Definition\Beam',
                'property' => 'aliasName',
                'expanded' => true,
                'multiple' => true
            ))
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Project',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_project';
    }
}