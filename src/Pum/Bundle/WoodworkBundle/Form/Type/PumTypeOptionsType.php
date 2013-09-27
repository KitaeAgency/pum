<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumTypeOptionsType extends AbstractType
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
        ));

        $resolver->setRequired(array('pum_type'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $features = $this->factory->getTypeHierarchy($options['pum_type'], 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        foreach ($features as $feature) {
            $feature->buildOptionsForm($builder);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use ($options) {
            $e->getForm()->setData(array());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_type_options';
    }
}
