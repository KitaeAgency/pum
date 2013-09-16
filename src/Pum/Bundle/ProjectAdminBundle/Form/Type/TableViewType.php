<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $builder->getData();
        $columns = array_merge(array('id'), $tableView->getColumnNames());
        $builder
            ->add($builder->create('tableview', 'section')
                ->add('name', 'text')
                ->add('private', 'checkbox')
            )
            ->add($builder->create('columns', 'section')
                ->add('columns', 'pa_tableview_columns', array('data' => $tableView))
            )
            ->add($builder->create('default_sort', 'section')
                ->add('default_sort_column', 'choice', array('choices' => array_combine($columns, $columns)))
                ->add('default_sort_order',  'choice', array('choices' => array('asc' => 'asc', 'desc' => 'desc')))
            )
            ->add($builder->create('filters', 'section')
                ->add('filters', 'pa_tableview_filters', array('data' => $tableView))
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
