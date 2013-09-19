<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Doctrine\ORM\QueryBuilder;

class IntegerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('unique', 'checkbox', array('required' => false))
            ->add('min', 'number', array('required' => false))
            ->add('max', 'number', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FormInterface $form)
    {
        $filterTypes = array(null, '=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('Choose an operator', 'equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $form
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'integer',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        if (isset($options['min']) || isset($options['max'])) {
            $metadata->addGetterConstraint($name, new Range(array('min' => $options['min'], 'max' => $options['max'])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'number');
    }
}
