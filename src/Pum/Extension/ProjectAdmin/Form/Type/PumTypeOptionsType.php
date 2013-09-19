<?php

namespace Pum\Extension\Form\Form\Type;

use Pum\Core\SchemaManager;
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
     * @var SchemaManager
     */
    protected $manager;

    public function __construct(SchemaManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['block_prefixes'] = array('form', 'pum_filter_default', 'pum_filter_'.$options['pum_type']);
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
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $e) use ($options) {
                $this->manager->getType($options['pum_type'])->buildOptionsForm($e->getForm());
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use ($options) {
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
