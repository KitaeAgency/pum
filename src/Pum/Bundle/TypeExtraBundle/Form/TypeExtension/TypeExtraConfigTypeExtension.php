<?php

namespace Pum\Bundle\TypeExtraBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extends form and give opportunity to pass block prefixes as options.
 */
class TypeExtraConfigTypeExtension extends AbstractTypeExtension
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add($builder->create('tabs', 'ww_tabs')
                ->add($builder->create('informations', 'ww_tab')*/
                    ->add('allowed_extra_type', 'checkbox', array(
                        'label'    => 'Authorized extra type '
                    ))
                /*)
            )*/
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
