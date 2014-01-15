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
            ->get('tabs')
                ->add($builder->create('project_admin', 'pum_tab')
                    /*->add('pa_logo', 'pum_media', array(
                        'label'     => 'Woodwork Logo',
                        'show_name' => false
                    ))*/
                    ->add('pa_default_pagination', 'number')
                    ->add('pa_pagination_values', 'collection', array(
                        'type' => 'number',
                        'allow_add' => true,
                        'allow_delete' => true,
                    ))
                )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'pum_form'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'pum_config';
    }
}
