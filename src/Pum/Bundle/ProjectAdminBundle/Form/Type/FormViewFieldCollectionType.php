<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Management of form view field collection
 */
class FormViewFieldCollectionType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'         => 'pa_formview_field',
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
        return 'pa_formview_field_collection';
    }
}
