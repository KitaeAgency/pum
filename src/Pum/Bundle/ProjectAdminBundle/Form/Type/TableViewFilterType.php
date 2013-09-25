<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $options['table_view'];

        $builder
            ->add('column', 'choice', array(
                'choice_list' => new ObjectChoiceList($tableView->getColumns(), 'label', array(), null, 'id')
            ))
            ->add('values', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewFilter',
            'table_view'  => null
        ));

        $resolver->setRequired(array('table_view'));
    }

    public function getName()
    {
        return 'pa_tableview_filter';
    }
}
