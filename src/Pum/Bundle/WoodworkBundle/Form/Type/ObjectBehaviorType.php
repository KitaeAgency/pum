<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ObjectBehaviorType extends AbstractType
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getData() instanceof \Pum\Core\Definition\ObjectDefinition) {
            throw new \RuntimeException('ObjectDefinition is required'); // misconfigured
        }

        $behaviorNames = $this->objectFactory->getBehaviorNames();
        foreach ($behaviorNames as $behaviorName => $behaviorServiceId) {
            $behavior = $this->objectFactory->getBehavior($behaviorName);

            $behavior->buildForm($builder, $options);
        }

        if ($options['with_submit']) {
            $builder->add('save', 'submit');
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Pum\Core\Definition\ObjectDefinition',
            'translation_domain' => 'pum_form',
            'with_submit'        => true
        ));
    }

    public function getName()
    {
        return 'ww_object_behavior';
    }
}
