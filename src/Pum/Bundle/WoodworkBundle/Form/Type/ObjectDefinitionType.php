<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ObjectDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectDefinition = $builder->getData();

        if (null !== $objectDefinition) {
            $builder
                ->add($builder->create('tabs', 'pum_tabs')
                    ->add($builder->create('overall', 'pum_tab')
                        ->add('name', 'text')
                        ->add('classname', 'text')
                        ->add('fields', 'ww_field_definition_collection')
                    )
                    ->add($builder->create('behaviors', 'pum_tab')
                        ->add($builder->create('seo', 'section')
                            ->add('seo', 'ww_object_definition_seo', array(
                                'label' => ' ',
                                'objectDefinition' => $objectDefinition,
                                'rootDir'     => $options['rootDir'],
                                'bundlesName' => $options['bundlesName']
                            ))
                        )
                        ->add($builder->create('security_user', 'section')
                            ->add('user_security', 'ww_object_definition_security_user', array(
                                'label' => ' ',
                                'objectDefinition' => $objectDefinition
                            ))
                        )
                    )
                )
            ;
        } else {
            $builder
                ->add('name', 'text')
                ->add('classname', 'text')
                ->add('fields', 'ww_field_definition_collection')
            ;
        }

        $builder
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'Pum\Core\Definition\ObjectDefinition',
            'rootDir'     => null,
            'bundlesName' => null
        ));
    }

    public function getName()
    {
        return 'ww_object_definition';
    }
}
