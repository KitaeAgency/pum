<?php

namespace Pum\Bundle\AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TabType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'virtual' => true
        ));
    }

    public function getName()
    {
        return 'pum_tab';
    }
}
