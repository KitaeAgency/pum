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
            $builder->add($i, 'pum_filter', array(
                'label'    => $columnName,
                'data'     => (isset($filters[$columnName])) ? $filters[$columnName] : null,
                'pum_type' => $tableView->getObjectDefinition()->getField($tableView->getColumnField($columnName))->getType(),
                'mapped'   => false
            ));
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            if ($options['active_post_submit']) {
                $form = $event->getForm();

                $tableView = $options['table_view'];
                $tableView->removeFilters();

                foreach ($form as $subForm) {
                    $label = $subForm->getConfig()->getOption('label');
                    $tableView->addFilter($label, $subForm->getData());
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
