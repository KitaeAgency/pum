<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $builder->getData();

        switch ($options['form_type']) {
            case 'name':
                $builder
                    ->add($builder->create('tableview', 'section')
                        ->add('name', 'text')
                        ->add('private', 'checkbox')
                        ->add('create_default', 'checkbox', array(
                            'label'  => 'Create default column for each field',
                            'data'   => true,
                            'mapped' => false
                        ))
                    )
                ;

                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();
                    if (isset($data['tableview']['create_default']) && $data['tableview']['create_default']) {
                        $tableView = $event->getForm()->getData();

                        $i = 1;
                        foreach ($tableView->getObjectDefinition()->getFields() as $field) {
                            $tableView->createColumn($field->getName(), $field, TableViewField::DEFAULT_VIEW, $i++);
                        }
                    }
                });
            break;

            case 'columns':
                $builder
                    ->add($builder->create('tableview', 'section')
                        ->add('name', 'text')
                        ->add('private', 'checkbox')
                    )
                    ->add($builder->create('columns', 'section')
                        ->add('columns', 'pa_tableview_column_collection', array(
                            'options'      => array(
                                'table_view' => $tableView
                            )
                        ))
                    )
                ;
            break;

            case 'sort':
                $builder
                    ->add('default_sort', 'pa_tableview_sort', array(
                        'label'      => ' ',
                        'table_view' => $tableView
                    ))
                ;
            break;

            case 'filters':
                $i = 1;
                foreach ($tableView->getColumns() as $column) {
                    $builder->add($i++, 'pa_tableview_filter_collection', array(
                        'label'   => $column->getLabel(),
                        'mapped'  => false,
                        'options' => array(
                            'table_view_field' => $column
                        )
                    ));
                }

                $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    $form = $event->getForm();

                    foreach ($form as $subForm) {
                        foreach ($subForm as $columns) {
                            foreach ($columns->getData() as $filters) {
                                var_dump($subForm->getConfig()->getOption('label'));
                                foreach ($filters as $filter) {
                                    var_dump($filter);
                                }
                            }
                        }
                    }
                    die('ok');
                });
            break;
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
