<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $columns = array_merge(array('id'), $builder->getData()->getColumnNames());
        $builder
            ->add($builder->create('tableview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox')
            )
            ->add($builder->create('columns', 'section')
                ->add('columns', 'pa_tableview_columns', array('data' => $builder->getData()))
            )
            ->add($builder->create('default_sort', 'section')
                ->add('default_sort_column', 'choice', array('choices' => array_combine($columns, $columns)))
                ->add('default_sort_order',  'choice', array('choices' => array('asc' => 'asc', 'desc' => 'desc')))
            )
            ->add('save', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\TableView'
        ));
    }

    public function getName()
    {
        return 'pa_tableview';
    }
}
