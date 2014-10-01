<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $beam = $builder->getData();

        if (null === $beam) {
            $builder
                ->add('name', 'text', array('label' => 'ww.form.beam.alias.label'))
                ->add('color', 'pum_color')
                ->add('icon', 'pum_icon')
                ->add('save', 'submit')
            ;
        } else {
            switch ($options['type']) {
                case 'clone':
                    $builder
                        ->add('name', 'text', array('data' => '', 'label' => 'ww.form.beam.alias.label'))
                        ->add('color', 'pum_color')
                        ->add('icon', 'pum_icon')
                        ->add('save', 'submit')
                    ;
                    break;
                
                default:
                    $builder
                        ->add('alias', 'text', array('data' => $beam->getAliasName()))
                        ->add('name', 'text', array('disabled' => true))
                        ->add('color', 'pum_color')
                        ->add('icon', 'pum_icon')
                        ->add('save', 'submit')
                    ;
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Beam',
            'translation_domain' => 'pum_form',
            'type' => 'regular'
        ));
    }

    public function getName()
    {
        return 'ww_beam';
    }
}
