<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelationSchemaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relations', 'ww_relation_collection', array(
                'label' => ' ',
                'options' => array(
                    'relation_schema' => $builder->getData()
                )
            ))
            ->add('Save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Relation\RelationSchema'
        ));
    }

    public function getName()
    {
        return 'ww_relation_schema';
    }
}
