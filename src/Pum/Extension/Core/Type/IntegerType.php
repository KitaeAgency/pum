<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class IntegerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'min' => null,
            'max' => null,

            'doctrine_type'   => 'integer',
            'pa_form_type'    => 'number',
            'pa_form_options' => array('required' => false),
            'pa_validation_constraints' => function (Options $options)
            {
                $res = array(
                    new Type(array('type' => 'integer'))
                );

                if ($options['required']) {
                    $res[] = new NotBlank();
                }

                if ($options['min'] || $options['max']) {
                    $res[] = new Range(array('min' => $options['min'], 'max' => $options['max']));
                }

                return $res;
            },
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $choicesKey = array(null, '1', '0');
        $choicesValue = array('All', 'Yes', 'No');

        $form
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('min', 'number', array('required' => false))
            ->add('max', 'number', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FieldContext $context, FormBuilderInterface $builder)
    {
        $filterTypes = array(null, '=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('Choose an operator', 'equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $builder
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integer';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'simple';
    }
}
