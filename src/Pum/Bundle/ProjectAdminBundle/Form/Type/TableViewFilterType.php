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
        $type = $options['pum_type'];
        $features = $this->objectFactory->getTypeHierarchy($type, 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        foreach ($features as $feature) {
            $feature->buildFilterForm($builder);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'pum_type'         => 'text',
            'data_class'       => 'Pum\Core\Definition\View\TableViewFilter',
        ));
    }

    public function getName()
    {
        return 'pa_tableview_filter';
    }
}
