<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableViewFilterColumnCollectionType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type' => 'pa_tableview_filter_collection'
        ));
    }

    public function getParent()
    {
        return 'collection';
    }

    public function getName()
    {
        return 'pa_tableview_filter_column_collection';
    }
}