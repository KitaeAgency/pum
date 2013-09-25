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

        if ($options['form_type'] == 'name') {
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
        } elseif ($options['form_type'] == 'columns') {
            $builder
                ->add($builder->create('tableview', 'section')
                    ->add('name', 'text')
                    ->add('private', 'checkbox')
                )
                ->add($builder->create('columns', 'section')
                    ->add('columns', 'collection', array(
                        'type'         => 'pa_tableview_column',
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'options'      => array(
                            'table_view' => $tableView
                        )
                    ))
                )
            ;
        } elseif ($options['form_type'] == 'sort') {
            $builder
                ->add($builder->create('default_sort', 'section')
                    ->add('default_sort', 'pa_tableview_sort', array(
                        'label'      => ' ',
                        'table_view' => $tableView
                    ))
                )
            ;
        } elseif ($options['form_type'] == 'filters') {
            $sectionBuilder = $builder->create('Filters', 'section');
            $i = 1;
            foreach ($tableView->getColumns() as $column) {
                $sectionBuilder->add($builder->create($i++, 'form', array('mapped' => false))
                    ->add('column', 'text', array('data' => $column->getLabel(), 'disabled' => true))
                    ->add('filters', 'collection', array(
                        'type'         => 'pa_tableview_filter',
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'options'      => array(
                            'table_view'       => $tableView,
                            'table_view_field' => $column
                        )
                    ))
                );
            }
            $builder->add($sectionBuilder);
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
