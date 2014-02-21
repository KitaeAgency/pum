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
                    ->add('fromObject', 'entity', array(
                        'class'    => 'Pum\Core\Definition\ObjectDefinition',
                        'choice_list' => new ObjectChoiceList($relationSchema->getBeam()->getObjects(), 'name', array(), null, 'name')
                    ))
                    ->add('fromName', 'text')
                    ->add('fromType', 'choice', array(
                        'choices' => array_combine(Relation::getTypes(), Relation::getTypes()),
                    ))
                )
                ->add($builder->create('to', 'pum_tab')
                    ->add('toObject', 'entity', array(
                        'class'    => 'Pum\Core\Definition\ObjectDefinition',
                        'group_by' => 'beam.name',
                        'property' => 'name',
                    ))
                    ->add('toName', 'text', array('required' => false))
                )
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Relation\Relation',
            'translation_domain' => 'pum_form'
        ));

        $resolver->setRequired(array('relation_schema'));
    }

    public function getName()
    {
        return 'ww_relation';
    }
}
