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
            ->add($builder->create('tabs', 'ww_tabs')
                ->add($builder->create('informations', 'ww_tab')
                    ->add('name', 'text')
                )
                ->add($builder->create('beams', 'ww_tab')
                    ->add('beams', 'entity', array(
                        'label'    => 'Beams',
                        'class'    => 'Pum\Core\Definition\Beam',
                        'property' => 'name',
                        'expanded' => true,
                        'multiple' => true
                    ))
                )
            )
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Project'
        ));
    }

    public function getName()
    {
        return 'ww_project';
    }
}
