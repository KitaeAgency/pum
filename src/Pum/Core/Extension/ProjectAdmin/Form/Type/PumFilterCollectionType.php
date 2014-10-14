<?php

namespace Pum\Extension\ProjectAdmin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumFilterCollectionType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'         => 'pum_filter',
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_filter_collection';
    }
}
