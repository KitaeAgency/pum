<?php

namespace Pum\Bundle\AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColorType extends AbstractType
{
    private $colors = array('greensea', 'belizehole', 'amethyst', 'orange', 'pomegranate', 'asbestos', 'darkpink', 'sanguine');

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'expanded' => true,
            'choices' => array_combine($this->colors, $this->colors),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_color';
    }
}
