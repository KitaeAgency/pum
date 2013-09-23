<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewColumnsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $options['tableView'];

        $builder
            ->add('label', 'text')
            ->add('field', 'choice', array(
                'choice_list' => new ObjectChoiceList($tableView->getObjectDefinition()->getFields(), 'name', array(), null, 'name')
            ))
            ->add('sequence', 'number')
            ->add('view', 'text', array('disabled' => true, 'data' => TableViewField::DEFAULT_VIEW))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewField',
            'tableView'  => null
        ));

        $resolver->setRequired(array('tableView'));
    }

    public function getName()
    {
        return 'pa_tableview_columns';
    }
}
