<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;

class TextType extends AbstractType
{
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
