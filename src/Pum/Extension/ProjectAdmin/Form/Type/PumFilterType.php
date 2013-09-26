<?php

namespace Pum\Extension\ProjectAdmin\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Extension\Form\FormExtension;
use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumFilterType extends AbstractType
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['block_prefixes'] = array('form', 'pum_filter_default', 'pum_filter_'.$options['pum_type']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->objectFactory->getTypeHierarchy($options['pum_type'], 'Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface') as $type) {
            $type->buildFilterForm($builder);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'pum_type' => null,
        ));

        $resolver->setRequired(array('pum_type'));
    }

    public function getName()
    {
        return 'pum_filter';
    }
}
