<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class TableViewFilterType extends AbstractType
{
    protected $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tableViewField = $options['table_view_field'];

        $features = $this->objectFactory->getTypeHierarchy($tableViewField->getField()->getType(), 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        foreach ($features as $feature) {
            $feature->buildFilterForm($builder);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'       => 'Pum\Core\Definition\View\TableViewFilter',
            'table_view_field' => null
        ));

        $resolver->setRequired(array('table_view_field'));
    }

    public function getName()
    {
        return 'pa_tableview_filter';
    }
}
