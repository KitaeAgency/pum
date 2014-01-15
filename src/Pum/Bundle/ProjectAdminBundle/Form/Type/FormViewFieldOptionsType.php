<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormViewFieldOptionsType extends AbstractType
{
    /**
     * @var ObjectFactory
     */
    protected $factory;

    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'pum_type' => null,
            'form_view_field' => null,
            // 'label' => function (Options $options) {
            //     return 'Options for '.$options['pum_type'];
            // }
        ));

        $resolver->setRequired(array('pum_type', 'form_view_field'));
        $resolver->setAllowedTypes(array('form_view_field' => 'Pum\Core\Definition\View\FormViewField'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $features = $this->factory->getTypeHierarchy($options['pum_type'], 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        $formViewField = $options['form_view_field'];

        foreach ($features as $feature) {
            $feature->buildFormViewOptions($builder, $formViewField);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pa_formview_field_options';
    }
}
