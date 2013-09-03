<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extends form and give opportunity to pass block prefixes as options.
 */
class ProjectAdminConfigTypeExtension extends AbstractTypeExtension
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add($builder->create('tabs', 'ww_tabs')
                ->add($builder->create('informations', 'ww_tab')*/
                    ->add('pa_logo', 'file', array(
                        'label'    => 'ProjectAdmin Logo',
                    ))
                    ->add('pa_default_pagination', 'number', array(
                        'label'    => 'Default pagination value'
                    ))
                    ->add('pa_pagination_values', 'collection', array(
                        'type' => 'number',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'label'    => 'Pagination values'
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
