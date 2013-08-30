<?php

namespace Pum\Bundle\WoodworkBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extends form and give opportunity to pass block prefixes as options.
 */
class WoodworkConfigTypeExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $form
            ->add('ww_logo', 'file', array(
                'mapped'   => false,
                'attr'     => array('placeholder' => 'Choose your woodwork logo'),
                'required' => false
            ))
            ->add('ww_show_export_button', 'checkbox', array('mapped'   => false))
            ->add('ww_show_import_button', 'checkbox', array('mapped'   => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'pum_config';
    }
}
