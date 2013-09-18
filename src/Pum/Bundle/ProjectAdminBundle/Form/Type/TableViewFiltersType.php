<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\TableView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filters   = $builder->getData();
        $tableView = $options['table_view'];

        if (!$tableView instanceof TableView) {
            throw new \RuntimeException(sprintf('No table view set in form.'));
        }

        $columnNames = $tableView->getColumnNames();

        foreach ($columnNames as $i => $columnName) {
            $builder->add($i, 'pum_filter_collection', array(
                'label'    => $columnName,
                'options'  => array(
                    'pum_type' => $tableView->getObjectDefinition()->getField($tableView->getColumnField($columnName))->getType()
                )
            ));
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($tableView) {
            $filters = $event->getData();
            $columnNames = $tableView->getColumnNames();

            $data = array();
            foreach ($filters as $name => $filter) {
                $pos = array_search($name, $columnNames);
                if ($pos === false) {
                    //filter obsolete
                    continue;
                }

                $data[$pos] = $filter;
            }

            $event->setData($data);
        });


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            if ($options['active_post_submit']) {
                $form = $event->getForm();

                $tableView = $options['table_view'];
                $tableView->removeFilters();

                foreach ($form as $subForm) {
                    $label = $subForm->getConfig()->getOption('label');
                    $values = array();
                    foreach ($subForm as $value) {
                        $values[] = $value->getData();
                    }
                    $tableView->addFilter($label, $values);
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'active_post_submit' => true,
            'table_view'         => null,
            'mapped'             => false
        ));
    }

    public function getName()
    {
        return 'pa_tableview_filters';
    }
}
