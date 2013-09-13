<?php

namespace Pum\Core\Extension\Form\Form\Type;

use Pum\Core\SchemaManager;
use Pum\Core\Extension\Form\FormExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumFilterType extends AbstractType
{
    /**
     * @var SchemaManager
     */
    protected $manager;

    public function __construct(SchemaManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $e) use ($options) {
                $this->manager->getType($options['pum_type'])->buildFormFilter($e->getForm());
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $e) use ($options) {
                $e->getForm()->setData(array());
            });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'pum_type' => null,
            'block_prefixes' => function (Options $options) {
                return array('form', 'pum_filter_default', 'pum_filter_'.$options['pum_type']);
            }
        ));

        $resolver->setRequired(array('pum_type'));
    }

    public function getName()
    {
        return 'pum_filter';
    }
}
