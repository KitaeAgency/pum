<?php

namespace Pum\Core\Extension\Core\Type;

use Pum\Core\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatetimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            '_doctrine_type' => 'datetime',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'date';
    }
}
