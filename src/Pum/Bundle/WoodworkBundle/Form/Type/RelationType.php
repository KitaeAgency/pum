<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('from', 'entity', array(
                'label'    => 'From object',
                'class'    => 'Pum\Core\Definition\ObjectDefinition',
                'property' => 'name',
                'value'    => 'name'
            ))*/
            ->add('from', 'text', array(
                'label'    => 'From object',
            ))
            ->add('fromName', 'text', array(
                'label'    => 'From field',
            ))
            /*->add('to', 'entity', array(
                'label'    => 'To object',
                'class'    => 'Pum\Core\Definition\ObjectDefinition',
                'property' => 'name'
            ))*/
            ->add('to', 'text', array(
                'label'    => 'To object',
            ))
            ->add('toName', 'text', array(
                'label'    => 'To field',
            ))
            ->add('type', 'choice', array(
                    'choices'   => array(
                        Relation::ONE_TO_MANY  => 'one-to-many',
                        Relation::MANY_TO_ONE  => 'many-to-one',
                        Relation::MANY_TO_MANY => 'many-to-many'
            )))
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Relation'
        ));
    }

    public function getName()
    {
        return 'ww_relation';
    }
}
