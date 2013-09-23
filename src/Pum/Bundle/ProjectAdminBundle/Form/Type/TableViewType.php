<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Definition\View\TableViewField;

class TableViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $builder->getData();

        if ($options['form_type'] == 'name') {
            $builder
                ->add($builder->create('tableview', 'section')
                    ->add('name', 'text')
                    ->add('private', 'checkbox')
                )
            ;

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $tableView = $event->getData();

                $i = 1;
                foreach ($tableView->getObjectDefinition()->getFields() as $field) {
                    $tableView->createColumn($field->getName(), $field, TableViewField::DEFAULT_VIEW, $i++);
                }
            });
        } elseif ($options['form_type'] == 'columns') {
            $builder
                ->add($builder->create('tableview', 'section')
                    ->add('name', 'text')
                    ->add('private', 'checkbox')
                )
                ->add($builder->create('columns', 'section')
                    ->add('columns', 'collection', array(
                        'type'         => 'pa_tableview_columns',
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'options'      => array(
                            'tableView' => $tableView
                        )
                    ))
                )
            ;
        } else {
            $builder
                ->add($builder->create('default_sort', 'section')
                    ->add('default_sort_column', 'choice', array('choices' => array_combine($columns, $columns)))
                    ->add('default_sort_order',  'choice', array('choices' => array('asc' => 'asc', 'desc' => 'desc')))
                )
                ->add($builder->create('filters', 'section')
                    ->add('filters', 'pa_tableview_filters', array(
                        'data'       => $tableView->getFilters(),
                        'table_view' => $tableView
                    ))
                )
            ;
        }

        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableView',
            'form_type'  => 'name'
        ));
    }

    public function getName()
    {
        return 'pa_tableview';
    }
}
