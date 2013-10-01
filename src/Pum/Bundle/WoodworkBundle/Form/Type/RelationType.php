<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Relation\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class RelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $relationSchema = $options['relation_schema'];

        $builder
            ->add($builder->create('tabs', 'pum_tabs')
                ->add($builder->create('from', 'pum_tab')
                    ->add('fromName', 'text', array(
                        'label' => 'Name'
                    ))
                    ->add('fromObject', 'choice', array(
                        'label'       => 'Object',
                        'choice_list' => new ObjectChoiceList($relationSchema->getBeam()->getObjects(), 'name', array(), null, 'name')
                    ))
                    ->add('fromBeam', 'text', array(
                        'data'     => $relationSchema->getBeam()->getName(),
                        'label'    => 'Beam',
                        'disabled' => true
                    ))
                    ->add('fromType', 'choice', array(
                        'choices' => array_combine(Relation::getTypes(), Relation::getTypes()),
                        'label'   => 'Relation type'
                    ))
                )
                ->add($builder->create('to', 'pum_tab')
                    ->add('toName', 'text', array(
                        'label' => 'Inverse by'
                    ))
                    ->add('toObject', 'entity', array(
                        'class'    => 'Pum\Core\Definition\ObjectDefinition',
                        'group_by' => 'beam.name',
                        'property' => 'name',
                        'label'    => 'Object'
                    ))
                    ->add('toBeam', 'entity', array(
                        'class'    => 'Pum\Core\Definition\Beam',
                        'property' => 'name',
                        'label'    => 'Beam'
                    ))
                    ->add('toType', 'choice', array(
                        'choices' => array_combine(Relation::getTypes(), Relation::getTypes()),
                        'label'   => 'Inverse relation type'
                    ))
                )
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Relation\Relation'
        ));

        $resolver->setRequired(array('relation_schema'));
    }

    public function getName()
    {
        return 'ww_relation';
    }
}