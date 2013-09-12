<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\TableView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewColumnsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $builder->getData();

        if (!$tableView instanceof TableView) {
            throw new \RuntimeException(sprintf('No table view set in form.'));
        }

        $definition  = $tableView->getObjectDefinition();
        $columnNames = $tableView->getColumnNames();

        foreach ($definition->getFields() as $field) {
            foreach ($columnNames as $columnName) {
                if ($tableView->hasColumn($columnName) && $tableView->getColumnField($columnName) == $field->getName()) {
                    continue 2;
                }
            }

            $columnNames[] = $field->getName();
        }

        foreach ($columnNames as $i => $columnName) {
            $builder->add($builder->create($i, 'form', array('mapped' => false))
                ->add('order', 'number', array('data' => $i + 1))
                ->add('label', 'text', array('data' => $columnName))
                ->add('show', 'checkbox', array('data' => $tableView->hasColumn($columnName)))
                ->add('field', 'text', array('data' => $tableView->hasColumn($columnName) ? $tableView->getColumnField($columnName) : $columnName, 'disabled' => true))
                ->add('view', 'text', array('data' => 'default', 'disabled' => true))
            );
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $columns = array();
            foreach ($form as $subForm) {
                $columns[$subForm->get('label')->getData()] = array(
                    'order' => $subForm->get('order')->getData(),
                    'show' => $subForm->get('show')->getData(),
                    'field' => $subForm->get('field')->getData(),
                    'view' => $subForm->get('view')->getData(),
                );
            }

            uasort($columns, function ($left, $right) {
                return $left['order'] > $right['order'];
            });

            $tableView = $event->getData();

            $tableView->removeColumns();

            foreach ($columns as $name => $column) {
                if ($column['show']) {
                    $tableView->addColumn($name, $column['field'], $column['view']);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\TableView',
            'mapped' => false
        ));
    }

    public function getName()
    {
        return 'pa_tableview_columns';
    }
}
