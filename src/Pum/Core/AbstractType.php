<?php

namespace Pum\Core;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'simple';
    }
}
