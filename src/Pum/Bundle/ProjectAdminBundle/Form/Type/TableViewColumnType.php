<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\TableViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

/**
 * Edition of a table view column
 */
class TableViewColumnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView = $options['table_view'];

        $builder
            ->add('label', 'text')
            ->add('field', 'choice', array(
                'choice_list' => new ObjectChoiceList($tableView->getObjectDefinition()->getFields(), 'name', array(), null, 'name')
            ))
            ->add('sequence', 'number', array(
                'attr' => array(
                    'data-sequence' => 'true'
                )
            ))
            ->add('view', 'text', array('disabled' => true, 'data' => TableViewField::DEFAULT_VIEW))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Core\Definition\View\TableViewField',
            'table_view'  => null,
            'translation_domain' => 'pum_form'
        ));

        $resolver->setRequired(array('table_view'));
    }

    public function getName()
    {
        return 'pa_tableview_column';
    }
}
