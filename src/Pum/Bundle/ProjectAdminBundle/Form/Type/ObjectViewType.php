<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $columns = array_merge(array('id'), $builder->getData()->getColumnNames());
        $builder
            ->add($builder->create('objectview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox')
            )
            ->add($builder->create('columns', 'section')
                ->add('columns', 'pa_objectview_columns', array('data' => $builder->getData()))
            )
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\ObjectView'
        ));
    }

    public function getName()
    {
        return 'pa_objectview';
    }
}
