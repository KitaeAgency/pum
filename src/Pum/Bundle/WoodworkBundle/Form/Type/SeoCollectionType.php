<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SeoCollectionType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'         => 'ww_seo',
            'allow_add'    => false,
            'allow_delete' => false,
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
        return 'ww_seo_collection';
    }
}
