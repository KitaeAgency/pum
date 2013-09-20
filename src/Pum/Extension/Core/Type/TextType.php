<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'max_length' => null,
            'multilines' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'simple';
    }
}
