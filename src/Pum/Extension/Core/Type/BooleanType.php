<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Type;

class BooleanType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'emf_type'        => 'boolean',
            'pa_form_type'    => 'checkbox',
            'pa_form_options' => array('required' => false),
            'pa_validation_constraints' => array(
                new Type(array('type' => 'boolean'))
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $choicesKey = array(null, '1', '0');
        $choicesValue = array('All', 'Yes', 'No');

        $builder
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    public function getName()
    {
        return 'boolean';
    }

    public function getParent()
    {
        return 'simple';
    }
}
