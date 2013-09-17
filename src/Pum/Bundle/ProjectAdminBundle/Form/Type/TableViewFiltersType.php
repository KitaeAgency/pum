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
        $tableView = $builder->getData();

        if (!$tableView instanceof TableView) {
            throw new \RuntimeException(sprintf('No table view set in form.'));
        }

        $columnNames = $tableView->getColumnNames();
        $filters     = $tableView->getFilters();

        foreach ($columnNames as $i => $columnName) {
            $builder->add($builder->create($i, 'form', array('mapped' => false))
                ->add('column', 'text', array('data' => $columnName, 'disabled' => true))
                ->add('filter', 'pum_filter', array(
                    'data'     => $tableView->getFilterValue($columnName),
                    'pum_type' => $tableView->getObjectDefinition()->getField($tableView->getColumnField($columnName))->getType()
                ))
            );
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $tableView = $event->getData();

            $tableView->removeFilters();

            foreach ($form as $subForm) {
                $tableView->addFilter($subForm->get('column')->getData(), $subForm->get('filter')->getData());
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
        return 'pa_tableview_filters';
    }
}
