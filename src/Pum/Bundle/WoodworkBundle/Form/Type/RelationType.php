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
            ->add('from', 'text')
            ->add('fromName', 'text')
            ->add('to', 'text')
            ->add('toName', 'text')
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
