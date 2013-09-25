<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewSortType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $options['table_view'];

        $builder
            ->add('column', 'choice', array(
                'choice_list' => new ObjectChoiceList($tableView->getColumns(), 'label', array(), null, 'label')
            ))
            ->add('order', 'choice', array('choices' => array('asc' => 'asc', 'desc' => 'desc')))
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use($options) {
            $tableView = $options['table_view'];

            $tableViewSort = $event->getForm()->getData();
            $tableViewSort->setTableview($tableView);
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewSort',
            'table_view'  => null
        ));

        $resolver->setRequired(array('table_view'));
    }

    public function getName()
    {
        return 'pa_tableview_sort';
    }
}
