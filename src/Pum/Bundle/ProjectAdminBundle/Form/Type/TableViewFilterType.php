<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\BuilderRegistryInterface;
use Pum\Core\Definition\View\TableView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewFilterType extends AbstractType
{
    protected $registry;

    public function __construct(BuilderRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableView      = $options['table_view'];
        $tableViewField = $options['table_view_field'];

        /*var_dump($this->registry->getTypeNames());
        die('ok');*/

        $filterTypes = array(null, '=', '<>');
        $filterNames = array('Choose an operator', 'equal', 'different');

        $builder
            ->add('type', 'choice', array(
                'choices' => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'       => 'Pum\Core\Definition\View\TableViewFilter',
            'table_view'       => null,
            'table_view_field' => null
        ));

        $resolver->setRequired(array('table_view', 'table_view_field'));
    }

    public function getName()
    {
        return 'pa_tableview_filter';
    }
}
